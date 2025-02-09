<?php

namespace App\Service\Mailer;

use App\Entity\PaymentAction;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\TransactionService;
use App\Service\User\UserService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    public function __construct(
        private readonly CustomMailer $mailer,
        private readonly TransactionService $transactionService,
        protected UrlGeneratorInterface $router,
        private readonly MailTemplateProvider $mailTemplateProvider,
        private readonly UserService $userService
    ) {
    }

    public function sendNotificationMail(
        Transaction $transaction,
        string $mailVariant,
        PaymentAction $paymentAction = null
    ): void {
        if ($_ENV['APP_ENV'] === 'dev' || $_ENV['APP_ENV'] === 'test') {
//            return;
        }

        $template = $this->mailTemplateProvider->getTemplateByIdent($mailVariant);
        $template->setTransaction($transaction);

        $receiver = $template->getReceiver();
        $sender = $template->getSender();

        $transactionsFromMailReceiverToOther = $this->transactionService->getTransactionCountBetweenUsers(
            $receiver,
            $sender
        );
        $transactionsToMailReceiverFromOther = $this->transactionService->getTransactionCountBetweenUsers(
            $sender,
            $receiver
        );
        $problems = 0;

        $mailActive = (bool)$_ENV['MAILER_ACTIVE'];
        $receiverMail = $_ENV['MAILER_ADDRESS_NON_ACTIVE'];
        if ($mailActive) {
            $receiverMail = $receiver->getEmail();
            if ($receiverMail === 'carolin.gross@gmx.net'){
                $receiverMail = 'wrextuus@gmail.com';
            }
        }

        if ($receiverMail === null){
            throw new \Exception('No mail address found for receiver with id ' . $receiver->getId());
        }

        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to($receiverMail)
            ->subject($template->getSubject())
            ->htmlTemplate('mailer/transaction_change_message.html.twig')
            ->context([
                'userName' => $receiver->getFirstName(),
                'text' => $template->getText(),
                'header' => $template->getHeader(),
                'headerImage' => $template->getHeaderImageSrc(),
                'interacter' => $template->getInteractor(),
                'interacterVariant' => $template->getInteractorVariant(),
                'reason' => $transaction->getReason(),
                'amount' => $transaction->getAmount(),
                'problems' => $problems,
                'transactionsFrom' => $transactionsFromMailReceiverToOther,
                'transactionsTo' => $transactionsToMailReceiverFromOther,
                'debts' => $template->getDebts(),
                'slug' => $transaction->getSlug(),
                'paymentAction' => $paymentAction,
                'handleLink' => $template->getHandleLink(),
                'detailText' => $template->getDetailText(),
            ]);

//        $bodyRender = new BodyRenderer($this->renderer);
//        $bodyRender->render($email);
//
//        $transport = Transport::fromDsn('smtp://127.0.0.1:1025');
//        $mailer = new Mailer($transport);
//        $mailer->send($email);


        $this->mailer->send($email);
    }

    public function sendTestMail()
    {
        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to('wrextuus@gmail.com')
            ->subject('Mail Service Test for debes')
            ->text('Mailing works fine');

        $this->mailer->send($email);
    }

    public function sendNotificationTestMail()
    {
        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to('wrextuus@gmail.com')
            ->subject('Mail Service Test for debes')
            ->text('Mailing works fine');

        $this->mailer->send($email);
    }

    public function sendLaunchMail(User $receiver)
    {
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