<?php /** @noinspection LanguageDetectionInspection */

namespace App\Command;

use App\Cdn\CloudinaryService;
use App\Entity\User;
use App\Service\Mailer\MailService;
use App\Service\Transaction\TransactionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'debes:simon:dillan')]
class SimonDillanCommand extends Command
{

    /**
     * LoadFixtureFilesToDatabase constructor.
     */
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private MailService $mailService,
        private TransactionService $transactionService,
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
        $user = new User();
        $user->setFirstName("Pitter");

        $transaction = $this->transactionService->getTransactionById(8);

        $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_REMINDER);

//        $imagePath = 'public/assets/img/home/borrow.png';
//        $cdnPath = 'debes/app/borrow.png';
//        $fileName = 'home_page_1';
//
//        $img = $this->cloudinaryService->getImageFromCdn($cdnPath, 500, 500);
//        dd($img);

//        $client = new Client();
//        $response = $client->request('GET', 'https://www.vita34.de/namenslisten/vornamen-fuer-jungen/#letter-B');
//        $content = $response->getBody()->getContents();
//        $crawler = new Crawler($content);
//        $names = $crawler->filter('li');
//
//        $charsToExclude = ['l', 'L', 'w', 'W', 'x', 'X', 'y', 'Y'];
//        $possibleNames = [];
//        foreach ($names as $name){
//            $plainName = trim($name->nodeValue);
//            $invalid = false;
//            foreach ($charsToExclude as $charToExclude){
//                if (str_contains($plainName, $charToExclude)){
//                    $invalid = true;
//                }
//                if (strlen($plainName) !== 5){
//                    $invalid = true;
//                }
//            }
//            if (!$invalid){
//                $possibleNames[] = $plainName;
//            }
//        }

        return 0;
    }
}
//TODO Es wäre schön wenn wir die exchanges auf den seiten farblich markiert darstellen könnten rot für eine Verrechnung von uns und grün für eine von jemand anderem
//TODO Es wäre schön wenn wir beim anlegen einer neuen Transaktion eine Gruppe von Personen angeben könnte, zwischen denen die Summe aufgeteilt  wird. Im standard für jeden gleich. Noch besser wäre es, wenn wir für jeden Schuldner den Betrag individuell anpassen könnten
//TODO Mahnmails versenden wenn geld nicht überwiesen wird
//TODO reminder per email
//TODO einstellungen, wegen mail benachrichtigungen oder automatischen Verrechnen von neuen Transaktionen
//TODO History
//TODO Umschulden: Es ist möglich seine vergebenen Kredite so zu bearbeiten, dass man sie einer neue Person zuweisen kann falls es sich um ein multi transaction handelt
/*
 *
 */