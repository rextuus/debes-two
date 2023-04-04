<?php

namespace App\Service\Mailer;

use App\Entity\PaymentAction;
use App\Entity\Transaction;
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
    public const MAIL_DEBT_CREATED = 'debt_created';
    public const MAIL_DEBT_CANCELED = 'debt_canceled';
    public const MAIL_DEBT_ACCEPTED = 'debt_accepted';
    public const MAIL_DEBT_DECLINED = 'debt_declined';
    public const MAIL_DEBT_PAYED_PAYPAL = 'debt_payed_paypal';
    public const MAIL_DEBT_PAYED_ACCOUNT = 'debt_payed_account';
    public const MAIL_DEBT_TRANSFERRED = 'debt_transferred';
    public const MAIL_DEBT_CONFIRMED = 'debt_confirmed';
    public const MAIL_DEBT_REMINDER = 'debt_reminder';

    public function __construct(private CustomMailer $mailer, private TransactionService $transactionService)
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
        $template = '';
        $slug = $transaction->getSlug();

        switch ($mailVariant) {
            case self::MAIL_DEBT_CREATED:
                $text = 'Es gibt leider schlechte Nachrichten. Jemand hat eine neue Schuldlast für deinen Debes-Account hinterlegt';
                $subject = 'Neue Schulden';
                $template = 'mailer/mail.created.html.twig';
                $receiver = $transaction->getDebtor();
                $sender = $transaction->getLoaner();
                break;
            case self::MAIL_DEBT_CANCELED:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuldlast für deinen Debes-Account zurückgezogen';
                $subject = 'Schuld zurückgezogen';
                $template = 'mailer/mail.canceled.html.twig';
                $receiver = $transaction->getDebtor();
                $sender = $transaction->getLoaner();
                break;
            case self::MAIL_DEBT_ACCEPTED:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuldenforderung von dir akzeptiert';
                $subject = 'Schuldlast akzeptiert ';
                $template = 'mailer/mail.accepted.html.twig';
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                break;
            case
            self::MAIL_DEBT_DECLINED:
                $text = 'Es gibt schlechte Nachrichten. Jemand hat eine Schuldenforderung von dir abgewiesen';
                $subject = 'Schuldlast abgelehnt ';
                $template = 'mailer/mail.declined.html.twig';
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                break;
            case self::MAIL_DEBT_TRANSFERRED:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuld beglichen und dir Geld überwiesen';
                $subject = 'Schulden zurück erhalten';
                $template = 'mailer/mail.transferred.html.twig';
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                break;
            case self::MAIL_DEBT_PAYED_ACCOUNT:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuld beglichen und dir Geld auf dein Bank-Konto überwiesen';
                $subject = 'Schulden zurück erhalten';
                $template = 'mailer/mail.transferred.html.twig';
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                break;
            case self::MAIL_DEBT_PAYED_PAYPAL:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuld beglichen und dir Geld auf dein Paypal-Konto überwiesen';
                $subject = 'Schulden zurück erhalten';
                $template = 'mailer/mail.transferred.html.twig';
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                break;
            case self::MAIL_DEBT_CONFIRMED:
                $text = 'Es gibt gute Nachrichten. Jemand hat den Eingang deiner Schuldrückzahlung bestätigt';
                $subject = 'Geldeingang bestätigt';
                $template = 'mailer/mail.confirmed.html.twig';
                $receiver = $transaction->getLoaner();
                $sender = $transaction->getDebtor();
                break;
            case self::MAIL_DEBT_REMINDER:
                $text = sprintf(
                    "Ein freundlicher Hinweis von <b>%s</b>!\n Es wäre toll, wenn du diesen Schuldeintrag nicht vergisst",
                    $transaction->getLoans()[0]->getOwner()->getFullName()
                );
                $header = 'Kleiner Reminder';
                $headerImage = '@images/reminder.jpg';
                $subject = 'Erinnerung nicht akzeptierte Schuld';
                $template = 'mailer/mail.base.html.twig';
                $receiver = $transaction->getDebtor();
                $sender = $transaction->getLoaner();
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

        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to($receiver->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
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
            ]);

//        $bodyRender = new BodyRenderer($this->renderer);
//        $bodyRender->render($email);
//
//        $transport = Transport::fromDsn('smtp://127.0.0.1:1025');
//        $mailer = new Mailer($transport);
//        $mailer->send($email);

        $this->mailer->send($email);
    }

    public function sendTestMail(){
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
}