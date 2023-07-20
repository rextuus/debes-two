<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Calculation;

use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class CashBox
{
    private User $owner;
    private float $amount;

    /**
     * @var Payment[]
     */
    private array $payments;

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): CashBox
    {
        $this->owner = $owner;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): CashBox
    {
        $this->amount = $amount;
        return $this;
    }

    public function getPayments(): array
    {
        return $this->payments;
    }

    public function setPayments(array $payments): CashBox
    {
        $this->payments = $payments;
        return $this;
    }

    public function addPaymentAction(Payment $paymentAction): void
    {
        $this->payments[] = $paymentAction;
    }
}