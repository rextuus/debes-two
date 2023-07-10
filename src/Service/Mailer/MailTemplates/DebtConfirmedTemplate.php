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
class DebtConfirmedTemplate extends AbstractMailTemplate implements MailTemplateInterface
{

    public function getName(): string
    {
        return MailService::MAIL_DEBT_CONFIRMED;
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
        return 'Eine Last fällt von deinen Schultern';
    }

    public function getHeaderImageSrc(): string
    {
        return '@images/handshake.png';
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
        return 'Geldeingang bestätigt';
    }

    public function getText(): string
    {
        return sprintf(
            'Es gibt gute Nachrichten. <b>%s</b> hat den Eingang deiner Schuldrückzahlung bestätigt',
            $this->getSender()->getFullName());
    }

    public function getHandleLink(): ?string
    {
        return null;
    }

    public function getDebts(): float
    {
        return $this->transactionService->getTotalDebtsBetweenUsers(
            $this->transaction->getDebtor(),
            $this->transaction->getLoaner()
        );
    }


}