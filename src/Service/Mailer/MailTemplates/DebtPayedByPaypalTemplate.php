<?php

declare(strict_types=1);

namespace App\Service\Mailer\MailTemplates;

use App\Entity\PaymentAction;
use App\Entity\User;
use App\Service\Mailer\AbstractMailTemplate;
use App\Service\Mailer\MailService;
use App\Service\Mailer\MailTemplateInterface;


class DebtPayedByPaypalTemplate extends AbstractMailTemplate implements MailTemplateInterface
{
    public function getName(): string
    {
        return MailService::MAIL_DEBT_PAYED_PAYPAL;
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
        return 'Zahltag!';
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
        return 'Schulden zurück erhalten';
    }

    public function getText(): string
    {
        return sprintf(
            'Es gibt gute Nachrichten. <b>%s</b> hat eine Schuld beglichen und dir Geld auf dein Paypal-Konto überwiesen',
            $this->getSender()->getFullName()
        );
    }

    public function getDetailText(): ?string
    {
        /** @var PaymentAction[] $payments */
        $payments = $this->transaction->getPaymentActions()->toArray();
        $payment = $payments[array_key_last($payments)];

        return sprintf('Das Geld wurde auf dein Paypalkonto <a href="www.paypal.de">%s</a> überwiesen', $payment->getPaypalAccountReceiver()->getEmail());
    }

    public function getHandleLink(): ?string
    {
        $params = ['slug' => $this->transaction->getSlug(), 'variant' => 'loaner'];
        $handleLink = $this->router->generate('transaction_confirm', $params);
        return self::BASE_URL . $handleLink;
    }

    public function getDebts(): float
    {
        return $this->transactionService->getTotalDebtsBetweenUsers(
            $this->transaction->getLoaner(),
            $this->transaction->getDebtor()
        );
    }
}