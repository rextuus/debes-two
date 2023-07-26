<?php

declare(strict_types=1);

namespace App\Service\Mailer\MailTemplates;

use App\Entity\User;
use App\Service\Mailer\AbstractMailTemplate;
use App\Service\Mailer\MailService;
use App\Service\Mailer\MailTemplateInterface;
use App\Service\Transaction\TransactionService;


class DebtCreatedReminderTemplate extends AbstractMailTemplate implements MailTemplateInterface
{

    public function getName(): string
    {
        return MailService::MAIL_DEBT_REMINDER;
    }

    public function getReceiver(): User
    {
        return $this->transaction->getDebtor();
    }

    public function getSender(): User
    {
        return $this->transaction->getLoaner();
    }

    public function getHeader(): string
    {
        return 'Kleiner Reminder';
    }

    public function getHeaderImageSrc(): string
    {
        return '@images/reminder.jpg';
    }

    public function getInteractor(): string
    {
        return $this->transaction->getLoaner()->getFirstName();
    }

    public function getInteractorVariant(): string
    {
        return self::INTERACTOR_LOANER_VARIANT;
    }

    public function getSubject(): string
    {
        return 'Erinnerung nicht akzeptierte Schuld';
    }

    public function getText(): string
    {
        return sprintf(
            'Ein freundlicher Hinweis von <b>%s</b>! Es wäre toll, wenn du diesen Schuldeintrag nicht vergisst',
            $this->getSender()->getFullName());
    }

    public function getHandleLink(): ?string
    {
        $params = ['slug' => $this->transaction->getSlug(), 'variant' => TransactionService::DEBTOR_VIEW];
        $handleLink = $this->router->generate('transaction_accept', $params);
        return self::BASE_URL . $handleLink;
    }

    public function getDebts(): float
    {
        return $this->transactionService->getTotalDebtsBetweenUsers(
            $this->transaction->getDebtor(),
            $this->transaction->getLoaner()
        );
    }


}