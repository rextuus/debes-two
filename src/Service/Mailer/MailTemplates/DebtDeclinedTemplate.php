<?php

declare(strict_types=1);

namespace App\Service\Mailer\MailTemplates;

use App\Entity\User;
use App\Service\Mailer\AbstractMailTemplate;
use App\Service\Mailer\MailService;
use App\Service\Mailer\MailTemplateInterface;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DebtDeclinedTemplate extends AbstractMailTemplate implements MailTemplateInterface
{

    public function getName(): string
    {
        return MailService::MAIL_DEBT_DECLINED;
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
        return 'Da stimmt was nicht';
    }

    public function getHeaderImageSrc(): string
    {
        return '@images/declined.jpg';
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
        return 'Schuldlast abgelehnt ';
    }

    public function getText(): string
    {
        return sprintf(
            'Es gibt schlechte Nachrichten. <b>%s</b> hat eine Schuldenforderung von dir abgewiesen',
            $this->getSender()->getFullName()
        );
    }

    public function getDebts(): float
    {
        return $this->transactionService->getTotalDebtsBetweenUsers(
            $this->transaction->getLoaner(),
            $this->transaction->getDebtor()
        );
    }
}