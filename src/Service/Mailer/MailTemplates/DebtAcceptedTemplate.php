<?php

declare(strict_types=1);

namespace App\Service\Mailer\MailTemplates;

use App\Entity\User;
use App\Service\Mailer\AbstractMailTemplate;
use App\Service\Mailer\MailService;
use App\Service\Mailer\MailTemplateInterface;


class DebtAcceptedTemplate extends AbstractMailTemplate implements MailTemplateInterface
{

    public function getName(): string
    {
        return MailService::MAIL_DEBT_ACCEPTED;
    }

    public function getReceiver(): User
    {
        return $this->transaction->getLoaner();
    }

    public function getSender(): User
    {
        return $this->transaction->getDebtor();
    }

    public function getHeader(): string
    {
        return 'Dein Einfluss zahlt sich aus!';
    }

    public function getHeaderImageSrc(): string
    {
        return '@images/handshake.png';
    }

    public function getInteractor(): string
    {
        return $this->transaction->getDebtor()->getFirstName();
    }

    public function getInteractorVariant(): string
    {
        return self::INTERACTOR_DEBTOR_VARIANT;
    }

    public function getSubject(): string
    {
        return 'Schuldlast akzeptiert';
    }

    public function getText(): string
    {
        return sprintf(
            'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuldenforderung von dir akzeptiert',
            $this->getSender()->getFullName()
        );
    }

    public function getHandleLink(): ?string
    {
        return null;
    }

    public function getDebts(): float
    {
        return $this->transactionService->getTotalDebtsBetweenUsers(
            $this->transaction->getLoaner(),
            $this->transaction->getDebtor()
        );
    }

    public function getDetailText(): ?string
    {
        return null;
    }
}