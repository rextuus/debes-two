<?php

declare(strict_types=1);

namespace App\Service\Mailer\MailTemplates;

use App\Entity\Exchange;
use App\Entity\User;
use App\Service\Exchange\ExchangeService;
use App\Service\Mailer\AbstractMailTemplate;
use App\Service\Mailer\MailService;
use App\Service\Mailer\MailTemplateInterface;
use App\Service\PaymentAction\PaymentActionService;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DebtExchangedTemplate extends AbstractMailTemplate implements MailTemplateInterface
{
    public function getName(): string
    {
        return MailService::MAIL_DEBT_EXCHANGED;
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
        return 'Ein fairer Austausch!';
    }

    public function getHeaderImageSrc(): string
    {
        return '@images/transferred.png';
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
        return 'Schulden verrechnet';
    }

    public function getText(): string
    {
        return sprintf(
            'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuld beglichen, indem sie mit einer anderen verrechnet wurde',
            $this->getSender()->getFullName()
        );
    }

    public function getDetailText(): ?string
    {
        /** @var Exchange[] $exchanges */
        $exchanges = $this->transaction->getExchanges()->toArray();
        $exchange = $exchanges[array_key_last($exchanges)];
        $remainingAmount = $exchange->getLoan()->getTransaction()->getAmount();
        $remainingText = sprintf(
            "Damit ist %s's Schuld für %s beglichen!",
            $this->getSender()->getFirstName(),
            $this->transaction->getReason()
        );
        if ($remainingAmount > 0.0) {
            $remainingText = sprintf(
                'Damit sind noch <b>%.2f €</b> deiner Leihe übrig, die dir %s weiterhin schuldet',
                $remainingAmount,
                $this->getSender()->getFirstName()
            );
        }


        $params = ['slug' => $this->transaction->getSlug(), 'variant' => 'loan'];
        $handleLink = $this->router->generate('transaction_detail', $params);
        $linkToLoan = self::BASE_URL . $handleLink;

        $params = ['slug' => $exchange->getDebt()->getTransaction()->getSlug(), 'variant' => 'debt'];
        $handleLink = $this->router->generate('transaction_detail', $params);
        $linkToDebt = self::BASE_URL . $handleLink;
        return sprintf(
            '<b>%s</b> hat <a href="%s">%s</a> mit <a href="%s">%s</a> verrechnet. %s',
            $this->getSender()->getFirstName(),
            $linkToLoan,
            $this->transaction->getReason(),
            $linkToDebt,
            $exchange->getDebt()->getTransaction()->getReason(),
            $remainingText
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
}