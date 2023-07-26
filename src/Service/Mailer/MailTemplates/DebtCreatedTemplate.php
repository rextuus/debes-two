<?php

declare(strict_types=1);

namespace App\Service\Mailer\MailTemplates;

use App\Entity\User;
use App\Service\Mailer\AbstractMailTemplate;
use App\Service\Mailer\MailService;
use App\Service\Mailer\MailTemplateInterface;


class DebtCreatedTemplate extends AbstractMailTemplate implements MailTemplateInterface
{

    public function getName(): string
    {
        return MailService::MAIL_DEBT_CREATED;
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
        return 'Du lebst wohl auf großem Fuße!';
    }

    public function getHeaderImageSrc(): string
    {
        return '@images/debt.png';
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
        return 'Neue Schulden';
    }

    public function getText(): string
    {
        return sprintf(
            'Es gibt leider schlechte Nachrichten. <b>%s</b> hat eine neue Schuldlast für deinen Debes-Account hinterlegt',
            $this->getSender()->getFullName());
    }

    public function getHandleLink(): ?string
    {
        $params = ['slug' => $this->transaction->getSlug(), 'variant' => 'debtor'];
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