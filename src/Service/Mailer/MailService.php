<?php

namespace App\Service\Mailer;

use App\Entity\PaymentAction;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\TransactionService;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\EventListener\MessageListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * MailService
 *
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class MailService
{

    private const DEBES_MAIL_ADDRESS = 'debes@wh-company.de';
    private const BASE_URL = 'https://debes.wh-company.de';
    public const MAIL_DEBT_CREATED = 'debt_created';
    public const MAIL_DEBT_CANCELED = 'debt_canceled';
    public const MAIL_DEBT_ACCEPTED = 'debt_accepted';
    public const MAIL_DEBT_DECLINED = 'debt_declined';
    public const MAIL_DEBT_PAYED_PAYPAL = 'debt_payed_paypal';
    public const MAIL_DEBT_PAYED_ACCOUNT = 'debt_payed_account';
    public const MAIL_DEBT_EXCHANGED = 'debt_exchanged';
    public const MAIL_DEBT_CONFIRMED = 'debt_confirmed';
    public const MAIL_DEBT_REMINDER = 'debt_reminder';

    public function __construct(private CustomMailer $mailer, private TransactionService $transactionService, protected UrlGeneratorInterface $router)
    {
//        $mailDsn = $_ENV['MAILER_DSN_REAL'];
//        $transport = Transport::fromDsn($mailDsn);
//        $this->mailer = new Mailer($transport);
    }

    /**
     * @param Transaction $transaction
     * @param string $mailVariant
     * @param PaymentAction|null $paymentAction
     * @throws TransportExceptionInterface
     */
    public function sendNotificationMail(Transaction $transaction, string $mailVariant, PaymentAction $paymentAction = null)
    {
        if ($_ENV['APP_ENV'] === 'dev' || $_ENV['APP_ENV'] === 'test') {
//            return;
        }
        $receiver = $transaction->getDebts()[0]->getOwner();

        $subject = '';
        $text = '';
        $handleLink = null;
        $slug = $transaction->getSlug();

        switch ($mailVariant) {
            case self::MAIL_DEBT_CREATED:
                $receiver = $transaction->getDebtor();
                $sender = $transaction->getLoaner();;
                $text = sprintf(
                    'Es gibt leider schlechte Nachrichten. <b>%s</b> hat eine neue Schuldlast für deinen Debes-Account hinterlegt',
                    $sender->getFullName()
                );
                $header = 'Du lebst wohl auf großem Fuße!';
                $headerImage = '@images/debt.png';

                $subject = 'Neue Schulden';

                $params = ['slug' => $transaction->getSlug(),'variant' => 'debtor'];
                $handleLink = $this->router->generate('transaction_accept', $params);
                $handleLink = self::BASE_URL.$handleLink;
                break;
            case self::MAIL_DEBT_CANCELED:
                $receiver = $transaction->getDebtor();
                $sender = $transaction->getLoaner();
                $text = sprintf(
                    'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuldlast für deinen Debes-Account zurückgezogen',
                    $sender->getFullName()
                );
                $header = 'Dein Einfluss zahlt sich aus!';
                $headerImage = '@images/decline.png';

                $subject = 'Schuld zurückgezogen';

                break;
            case self::MAIL_DEBT_ACCEPTED:
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();;

                $text = sprintf(
                    'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuldenforderung von dir akzeptiert',
                    $sender->getFullName()
                );
                $header = 'Dein Einfluss zahlt sich aus!';
                $headerImage = '@images/handshake.png';

                $subject = 'Schuldlast akzeptiert ';

                break;
            case
            self::MAIL_DEBT_DECLINED:
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();

                $text = sprintf(
                    'Es gibt schlechte Nachrichten. <b>%s</b> hat eine Schuldenforderung von dir abgewiesen',
                    $sender->getFullName()
                );
                $header = 'Da stimmt was nicht';
                $headerImage = '@images/declined.jpg';

                $subject = 'Schuldlast abgelehnt ';

                break;
            case self::MAIL_DEBT_EXCHANGED:
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                $text = sprintf(
                    'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuld beglichen in dem er sie mit einer anderen verrechnet hat',
                    $sender->getFullName()
                );
                $header = 'Ein fairer Austausch!';
                $headerImage = '@images/transferred.png';

                $subject = 'Schulden verrechnet';
                break;
            case self::MAIL_DEBT_PAYED_ACCOUNT:
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                $text = sprintf(
                    'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuld beglichen und dir Geld auf dein Bank-Konto überwiesen',
                    $sender->getFullName()
                );
                $header = 'Zahltag!';
                $headerImage = '@images/transferred.png';

                $subject = 'Schulden zurück erhalten';

                $params = ['slug' => $transaction->getSlug(),'variant' => 'debtor'];
                $handleLink = $this->router->generate('transaction_confirm', $params);
                $handleLink = self::BASE_URL.$handleLink;
                break;
            case self::MAIL_DEBT_PAYED_PAYPAL:
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                $text = sprintf(
                    'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuld beglichen und dir Geld auf dein Paypal-Konto überwiesen',
                    $sender->getFullName()
                );
                $header = 'Zahltag!';
                $headerImage = '@images/transferred.png';

                $subject = 'Schulden zurück erhalten';

                $params = ['slug' => $transaction->getSlug(),'variant' => 'debtor'];
                $handleLink = $this->router->generate('transaction_confirm', $params);
                $handleLink = self::BASE_URL.$handleLink;

                break;
            case self::MAIL_DEBT_CONFIRMED:
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                $text = sprintf(
                    "Es gibt gute Nachrichten. <b>%s</b> hat den Eingang deiner Schuldrückzahlung bestätigt",
                    $sender->getFullName()
                );
                $header = 'Zahltag!';
                $headerImage = '@images/handshake.png';

                $subject = 'Geldeingang bestätigt';

                break;
            case self::MAIL_DEBT_REMINDER:
                $receiver = $transaction->getDebtor();
                $sender = $transaction->getLoaner();

                $text = sprintf(
                    "Ein freundlicher Hinweis von <b>%s</b>!\n Es wäre toll, wenn du diesen Schuldeintrag nicht vergisst",
                    $sender->getFullName()
                );
                $header = 'Kleiner Reminder';
                $headerImage = '@images/reminder.jpg';
                $subject = 'Erinnerung nicht akzeptierte Schuld';

                $params = ['slug' => $transaction->getSlug()];
                $handleLink = $this->router->generate('transfer_overview', $params);
                $handleLink = self::BASE_URL.$handleLink;

                break;
        }

        $transactionsFromMailReceiverToOther = $this->transactionService->getTransactionCountBetweenUsers($receiver, $sender);
        $transactionsToMailReceiverFromOther = $this->transactionService->getTransactionCountBetweenUsers($sender, $receiver);
        $problems = 0;
        $debts = $this->transactionService->getTotalDebtsBetweenUsers($sender, $receiver);

//        $renderedHtml = $this->renderer->render(
//            $template,
//            [
//                'userName' => $receiver->getFirstName(),
//                'text' => $text,
//                'interacter' => $transaction->getLoans()[0]->getOwner()->getFirstName(),
//                'reason' => $transaction->getReason(),
//                'amount' => $transaction->getAmount(),
//                'problems' => $problems,
//                'transactions' => $transactions,
//                'debts' => $debts,
//                'slug' => $slug,
//                'paymentAction' => $paymentAction,
//            ]
//        );

        $mailActive = (bool)$_ENV['MAILER_ACTIVE'];
        $receiverMail = $_ENV['MAILER_ADDRESS_NON_ACTIVE'];
        if ($mailActive) {
            $receiverMail = $receiver->getEmail();
        }

        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to($receiverMail)
            ->subject($subject)
            ->htmlTemplate('mailer/transaction_change_message.html.twig')
            ->context([
                'userName' => $receiver->getFirstName(),
                'text' => $text,
                'header' => $header,
                'headerImage' => $headerImage,
                'interacter' => $transaction->getLoans()[0]->getOwner()->getFirstName(),
                'interacterVariant' => 'Schuldner',
                'reason' => $transaction->getReason(),
                'amount' => $transaction->getAmount(),
                'problems' => $problems,
                'transactionsFrom' => $transactionsFromMailReceiverToOther,
                'transactionsTo' => $transactionsToMailReceiverFromOther,
                'debts' => $debts,
                'slug' => $slug,
                'paymentAction' => $paymentAction,
                'handleLink' => $handleLink,
            ]);

//        $bodyRender = new BodyRenderer($this->renderer);
//        $bodyRender->render($email);
//
//        $transport = Transport::fromDsn('smtp://127.0.0.1:1025');
//        $mailer = new Mailer($transport);
//        $mailer->send($email);


        $this->mailer->send($email);
    }

    public function sendTestMail(User $receiver){
        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to('wrextuus@gmail.com')
            ->subject('Mail Service Test for debes')
            ->text('Mailing works fine');

//        $mailDsn = $_ENV['MAILER_DSN'];
//        dump($mailDsn);
//        $mailDsn = $_ENV['MAILER_DSN_REAL'];
//        dump($mailDsn);
//
//        $transport = Transport::fromDsn('smtp://0.0.0.0:1025');
//        $transport = Transport::fromDsn('smtp://debes@wh-company.de:M6264P687783D78@smtp.strato.de:465/?encryption=ssl$auth_mode=login');
//        $transport = Transport::fromDsn($mailDsn);
//        $transport = Transport::fromDsn('smtp://127.0.0.1:1025');
//        $mailer = new Mailer($transport);
////        dd($this->mailer);
///
        $this->mailer->send($email);
    }

    public function sendLaunchMail(User $receiver){
        $text = 'Launchparty';
        $header = 'Launchparty';

        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to('wrextuus@gmail.com')
            ->subject('DEBES 2.0 Launch')
            ->htmlTemplate('mailer/transaction_change_message.html.twig')
            ->context([
                'userName' => $receiver->getFirstName(),
                'text' => $text,
                'header' => $header,
                'headerImage' => '@images/debt.png',
            ]);
        $this->mailer->send($email);
    }
}