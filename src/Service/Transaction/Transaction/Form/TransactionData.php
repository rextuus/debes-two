<?php

namespace App\Service\Transaction\Transaction\Form;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\User;

class TransactionData
{

    private float $amount;

    private ?float $initialAmount;

    /**
     * @var Debt[]
     */
    private  array $debts;

    /**
     * @var Loan[]
     */
    private array $loans;

    private string $reason;

    private ?User $owner;

    private ?string $state;

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): TransactionData
    {
        $this->amount = $amount;
        return $this;
    }

    public function getInitialAmount(): ?float
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(?float $initialAmount): TransactionData
    {
        $this->initialAmount = $initialAmount;
        return $this;
    }

    public function getDebts(): array
    {
        return $this->debts;
    }

    public function setDebts(array $debts): TransactionData
    {
        $this->debts = $debts;
        return $this;
    }

    public function getLoans(): array
    {
        return $this->loans;
    }

    public function setLoans(array $loans): TransactionData
    {
        $this->loans = $loans;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): TransactionData
    {
        $this->reason = $reason;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): TransactionData
    {
        $this->owner = $owner;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): TransactionData
    {
        $this->state = $state;
        return $this;
    }
}
