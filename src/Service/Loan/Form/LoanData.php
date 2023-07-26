<?php

namespace App\Service\Loan\Form;

use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Service\Transaction\Transaction\Form\TransactionPartDataInterface;
use DateTimeInterface;

class LoanData implements TransactionPartDataInterface
{

    private float $amount;

    private DateTimeInterface $created;

    private ?DateTimeInterface $edited;

    private Transaction $transaction;

    private User $owner;

    private string $reason;

    private bool $paid;

    private string $state;

    private float $initialAmount;

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): LoanData
    {
        $this->created = $created;
        return $this;
    }

    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    public function setEdited(?DateTimeInterface $edited): LoanData
    {
        $this->edited = $edited;
        return $this;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): LoanData
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): LoanData
    {
        $this->owner = $owner;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): LoanData
    {
        $this->reason = $reason;
        return $this;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): LoanData
    {
        $this->paid = $paid;
        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getInitialAmount(): float
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(float $initialAmount): LoanData
    {
        $this->initialAmount = $initialAmount;
        return $this;
    }

    public function initFrom(TransactionPartInterface $loan): LoanData
    {
        $this->setCreated($loan->getCreated());
        $this->setEdited($loan->getCreated());
        $this->setAmount($loan->getAmount());
        $this->setCreated($loan->getCreated());
        $this->setOwner($loan->getOwner());
        $this->setPaid($loan->getPaid());
        $this->setTransaction($loan->getTransaction());
        $this->setState($loan->getState());

        return $this;
    }
}
