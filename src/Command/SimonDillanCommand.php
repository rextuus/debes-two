<?php /** @noinspection LanguageDetectionInspection */

namespace App\Command;

use App\Entity\Transaction;
use App\Service\Legacy\LegacyImportService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\User\UserData;
use App\Service\User\UserService;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * ImportLegacyDatabaseCommand
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 *
 */
class SimonDillanCommand extends Command
{
    const NAME = 'debes:simon:dillan';

    protected static $defaultName = self::NAME;


    /**
     * LoadFixtureFilesToDatabase constructor.
     */
    public function __construct(
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
        $client = new Client();
        $response = $client->request('GET', 'https://www.vita34.de/namenslisten/vornamen-fuer-jungen/#letter-B');
        $content = $response->getBody()->getContents();
        $crawler = new Crawler($content);
        $names = $crawler->filter('li');

        $charsToExclude = ['l', 'L', 'w', 'W', 'x', 'X', 'y', 'Y'];
        $possibleNames = [];
        foreach ($names as $name){
            $plainName = trim($name->nodeValue);
            $invalid = false;
            foreach ($charsToExclude as $charToExclude){
                if (str_contains($plainName, $charToExclude)){
                    $invalid = true;
                }
                if (strlen($plainName) !== 5){
                    $invalid = true;
                }
            }
            if (!$invalid){
                $possibleNames[] = $plainName;
            }
        }
        dump($possibleNames);
        dump(count($possibleNames));

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