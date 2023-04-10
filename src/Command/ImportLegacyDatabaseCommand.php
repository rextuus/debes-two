<?php /** @noinspection LanguageDetectionInspection */

namespace App\Command;

use App\Entity\Transaction;
use App\Service\Legacy\LegacyImportService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\User\UserData;
use App\Service\User\UserService;
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

    /**
     * @var LegacyImportService
     */
    private $legacyImportService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * LoadFixtureFilesToDatabase constructor.
     */
    public function __construct(
        LegacyImportService $legacyImportService,
        UserService         $userService
    )
    {
        parent::__construct();

        $this->legacyImportService = $legacyImportService;
        $this->userService = $userService;
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

        $userDatas = $this->getUserDataAndBankData($lines);

        $userDatas = $this->getTransferData($lines);

        return 0;
    }

    /**
     * @param array $lines
     * @return UserData[]
     */
    protected function getUserDataAndBankData(array $lines): array
    {
        $userDatas = [];
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
                $userDatas[$data[5]] = $userData;
            }
        }
        return $userDatas;
    }

    /**
     * @param array $lines
     * @return UserData[]
     */
    protected function getTransferData(array $lines): array
    {
        $userDatas = [];
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
                dump($data);

                $transactionData = new TransactionCreateData();
                $transactionData->setDebtors($data[1]);
                $transactionData->setLoaners($data[2]);
                $transactionData->setAmount($data[3]);
                $transactionData->setReason($data[5]);

                switch (trim($data[4])) {
                    case '1':
                        $transactionData->setState(Transaction::STATE_READY);
                        break;
                    case '2':
                        $transactionData->setState(Transaction::STATE_ACCEPTED);
                        break;
                    case '3':
                        $transactionData->setState(Transaction::STATE_CONFIRMED);
                        break;
                    case '4':
                        $transactionData->setState(Transaction::STATE_CLEARED);
                        break;
                }

                $userDatas[] = $transactionData;
                // TODO WAIT FOR HISTORY IS IMPLEMENTED
            }
        }
        return $userDatas;
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