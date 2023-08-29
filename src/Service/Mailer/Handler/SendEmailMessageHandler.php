<?php

namespace App\Service\Mailer\Handler;

use App\Service\Mailer\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(private readonly MailService $mailService) { }

    public function __invoke(SendEmailMessage $message): void
    {
        $this->mailService->sendNotificationMail(
            $message->getTransaction(),
            $message->getMailVariant(),
            $message->getPaymentAction()
        );
    }
}
