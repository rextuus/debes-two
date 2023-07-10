<?php /** @noinspection LanguageDetectionInspection */

namespace App\Command;

use App\Entity\PaymentAction;
use App\Entity\Transaction;
use App\Entity\TransactionStateChangeEvent;
use App\Service\Legacy\LegacyImportService;
use App\Service\PaymentAction\PaymentActionData;
use App\Service\PaymentAction\PaymentActionService;
use App\Service\PaymentOption\BankAccountData;
use App\Service\PaymentOption\BankAccountService;
use App\Service\PaymentOption\IbanValidationService;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventData;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionCreateLegacyImportData;
use App\Service\Transaction\TransactionService;
use App\Service\User\UserData;
use App\Service\User\UserService;
use DateTime;
use PHP_IBAN\IBAN;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ImportLegacyDatabaseCommand
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 *
 */
class ImportLegacyDatabaseCommand extends Command
{
    const NAME = 'debes:legacy:import';

    /**
     * @var string
     */
    protected static $defaultName = self::NAME;

    private array $idUserEntityRelations = [];

    private array $oldIdNewIdRelation = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        8 => 6,
        9 => 7,
        10 => 8,
        11 => 9,
        12 => 10,
        13 => 11,
        14 => 12,
    ];

    /**
     * LoadFixtureFilesToDatabase constructor.
     */
    public function __construct(
        private LegacyImportService           $legacyImportService,
        private UserService                   $userService,
        private BankAccountService            $bankAccountService,
        private TransactionService            $transactionService,
        private PaymentActionService          $paymentActionService,
        private TransactionChangeEventService $changeEventService,
        private IbanValidationService         $ibanValidationService,
    )
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Import legacy data from sql file');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $this->getApplication()->getKernel()->getProjectDir();

        $inputFile = $projectDir . '/schulden.sql';
        $rawContent = file_get_contents($inputFile);
        $lines = explode("\n", $rawContent);

        $userData = $this->getUserDataAndBankData($lines);
        $userAutoIncrementId = 1;
        foreach ($userData as $user => $data) {
            $user = $this->userService->storeUser($data['user']);
            /** @var BankAccountData $bankData */
            $bankData = $data['bank'];
            $bankData->setOwner($user);
            $this->bankAccountService->storeBankAccount($bankData);

            $this->idUserEntityRelations[$userAutoIncrementId] = $user;
            $userAutoIncrementId++;
        }

        $transactionDataSets = $this->getTransferData($lines);
        foreach ($transactionDataSets as $dataSet) {
            /** @var TransactionCreateLegacyImportData $transactionData */
            $transactionData = $dataSet['transaction'];
            $debtor = $this->userService->findUserById($this->oldIdNewIdRelation[$transactionData->getDebtors()]);
            $transactionData->setOwner($debtor);
            $loaner = $this->userService->findUserById($this->oldIdNewIdRelation[$transactionData->getLoaners()]);
            $transaction = $this->transactionService->storeSingleTransaction($transactionData, $loaner);

            foreach ($dataSet['changeEvents'] as $stateChangeEventData) {
                /** @var TransactionChangeEventData $stateChangeEventData */
                $stateChangeEventData->setTransaction($transaction);

                if ($stateChangeEventData->getType() === TransactionStateChangeEvent::TYPE_BANK_ACCOUNT) {

                    // create payment action
                    $paymentActionData = new PaymentActionData();
                    $paymentActionData->setVariant(PaymentAction::VARIANT_BANK);

                    $bankAccountSender = $this->bankAccountService->getBankAccountsOfUser($debtor)[0];
                    $bankAccountReceiver = $this->bankAccountService->getBankAccountsOfUser($loaner)[0];
                    $paymentActionData->setBankAccountSender($bankAccountSender);
                    $paymentActionData->setBankAccountReceiver($bankAccountReceiver);
                    $paymentActionData->setTransaction($transaction);

                    $paymentAction = $this->paymentActionService->storePaymentAction($paymentActionData);
                    $stateChangeEventData->setTarget($paymentAction);
                }
                $this->changeEventService->storeTransactionChangeEvent($stateChangeEventData);
            }
        }

        return 0;
    }

    /**
     * @param array $lines
     * @return UserData[]
     */
    protected function getUserDataAndBankData(array $lines): array
    {
        $dataSets = [];
        $sectionActive = false;
        foreach ($lines as $line) {
            if (str_contains($line, "INSERT INTO `users`")) {
                $sectionActive = true;
                continue;
            }
            if ($sectionActive) {
                if ($line === '') {
                    $sectionActive = false;
                    continue;
                }
                $data = explode(',', $line);

                array_walk(
                    $data,
                    function (&$value) {
                        // Remove all whitespace from the value
                        $value = preg_replace('/\s+/', '', $value);

                        // Remove all single and double quotes from the value
                        $value = str_replace(array("'", "\""), '', $value);
                    }
                );

                $userData = new UserData();
                $userData->setEmail($data[1]);
                $userData->setUserName($data[1]);
                $userData->setPassword($data[2]);
                $userData->setPassword($this->generateInitialPassword());

                if ($data[3] === 'Carolin') {
                    $userData->setPassword('Journey:08');
                }
                $userData->setFirstName($data[3]);
                $userData->setLastName($data[4]);
                $dataSets[$data[5]]['user'] = $userData;

                // iban
                $bankData = $this->ibanValidationService->validateIban($data[5]);
                $bankData->setAccountName($data[3] . ' ' . $data[4]);
                $bankData->setDescription('Konto ' . $bankData->getBankName() . ' - ' . $data[3] . ' ' . $data[4]);
                $dataSets[$data[5]]['bank'] = $bankData;
            }
        }
        return $dataSets;
    }

    /**
     * @param array $lines
     * @return UserData[]
     */
    protected function getTransferData(array $lines): array
    {
        $transactionToCreate = [];
        $sectionActive = false;
        foreach ($lines as $line) {
            if (str_contains($line, "INSERT INTO `kredit`")) {
                $sectionActive = true;
                continue;
            }
            if ($sectionActive) {
                if ($line === '') {
                    $sectionActive = false;
                    continue;
                }
                $data = explode(',', $line);

                // when , is in
                if (count($data) !== 9) {
                    $content = '';
                    $border = count($data);
                    for ($contentNr = 5; $contentNr <= $border - 4; $contentNr++) {
                        $content = $content . $data[$contentNr];
                        unset($data[$contentNr]);
                    }

                    $data = array_values($data);
                    $data[7] = $data[6];
                    $data[6] = $data[5];
                    $data[5] = $content;

                }

                array_walk(
                    $data,
                    function (&$value) {
                        // Remove all whitespace from the value
//                        $value = preg_replace('/\s+/', '', $value);

                        // Remove all single and double quotes from the value
                        $value = str_replace(array("'", "\""), '', $value);
                        $value = trim($value);
                    }
                );

                $transactionData = new TransactionCreateLegacyImportData();
                $transactionData->setDebtors($data[1]);
                $transactionData->setLoaners($data[2]);
                $transactionData->setAmount($data[3]);
                $transactionData->setInitialAmount($data[3]);
                $transactionData->setReason($data[5]);
                $transactionData->setCreated(new DateTime($data[6]));
                $editDate = str_replace(['(', ')'], '', $data[7]);
                $transactionData->setEdited(new DateTime($editDate));

                $transactionEvents = [];
                switch (trim($data[4])) {
                    case '1':
                        $transactionData->setState(Transaction::STATE_READY);
                        break;
                    case '2':
                        $transactionData->setState(Transaction::STATE_ACCEPTED);

                        $eventChangeData = new TransactionChangeEventData();
                        $eventChangeData->setOldState(Transaction::STATE_READY);
                        $eventChangeData->setNewState(Transaction::STATE_ACCEPTED);
                        $eventChangeData->setCreated(new DateTime($editDate));
                        $eventChangeData->setType(TransactionStateChangeEvent::TYPE_BLANK);
                        $transactionEvents[] = $eventChangeData;

                        break;
                    case '3':
                        $transactionData->setState(Transaction::STATE_CLEARED);

                        $eventChangeData = new TransactionChangeEventData();
                        $eventChangeData->setOldState(Transaction::STATE_READY);
                        $eventChangeData->setNewState(Transaction::STATE_ACCEPTED);
                        $eventChangeData->setCreated(new DateTime($editDate));
                        $eventChangeData->setType(TransactionStateChangeEvent::TYPE_BLANK);
                        $transactionEvents[] = $eventChangeData;

                        $eventChangeData = new TransactionChangeEventData();
                        $eventChangeData->setOldState(Transaction::STATE_ACCEPTED);
                        $eventChangeData->setNewState(Transaction::STATE_CLEARED);
                        $eventChangeData->setCreated(new DateTime($editDate));
                        $eventChangeData->setType(TransactionStateChangeEvent::TYPE_BANK_ACCOUNT);
                        $transactionEvents[] = $eventChangeData;

                        break;
                    case '4':
                        $transactionData->setAmount(0.0);

                        $transactionData->setState(Transaction::STATE_CONFIRMED);

                        $eventChangeData = new TransactionChangeEventData();
                        $eventChangeData->setOldState(Transaction::STATE_READY);
                        $eventChangeData->setNewState(Transaction::STATE_ACCEPTED);
                        $eventChangeData->setCreated(new DateTime($editDate));
                        $eventChangeData->setType(TransactionStateChangeEvent::TYPE_BLANK);
                        $transactionEvents[] = $eventChangeData;

                        $eventChangeData = new TransactionChangeEventData();
                        $eventChangeData->setOldState(Transaction::STATE_ACCEPTED);
                        $eventChangeData->setNewState(Transaction::STATE_CLEARED);
                        $eventChangeData->setCreated(new DateTime($editDate));
                        $eventChangeData->setType(TransactionStateChangeEvent::TYPE_BANK_ACCOUNT);
                        $transactionEvents[] = $eventChangeData;

                        $eventChangeData = new TransactionChangeEventData();
                        $eventChangeData->setOldState(Transaction::STATE_CLEARED);
                        $eventChangeData->setNewState(Transaction::STATE_CONFIRMED);
                        $eventChangeData->setCreated(new DateTime($editDate));
                        $eventChangeData->setType(TransactionStateChangeEvent::TYPE_BLANK);
                        $transactionEvents[] = $eventChangeData;
                        break;
                }

                $set = ['transaction' => $transactionData, 'changeEvents' => $transactionEvents];
                $transactionToCreate[] = $set;
                // TODO WAIT FOR HISTORY IS IMPLEMENTED
            }
        }
        return $transactionToCreate;
    }

    private function generateInitialPassword(): string
    {
        $length = 10;

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!$%&*_-=+:?';

        $password = substr(str_shuffle($chars), 0, $length);
        $password = '123Katzen';
        return $password;
    }
}
//TODO Es wäre schön wenn wir die exchanges auf den seiten farblich markiert darstellen könnten rot für eine Verrechnung von uns und grün für eine von jemand anderem
//TODO Es wäre schön wenn wir beim anlegen einer neuen Transaktion eine Gruppe von Personen angeben könnte, zwischen denen die Summe aufgeteilt  wird. Im standard für jeden gleich. Noch besser wäre es, wenn wir für jeden Schuldner den Betrag individuell anpassen könnten
//TODO Mahnmails versenden wenn geld nicht überwiesen wird
//TODO reminder per email
//TODO einstellungen, wegen mail benachrichtigungen oder automatischen Verrechnen von neuen Transaktionen
//TODO History
//TODO Umschulden: Es ist möglich seine vergebenen Kredite so zu bearbeiten, dass man sie einer neue Person zuweisen kann falls es sich um ein multi transaction handelt