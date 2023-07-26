<?php

namespace App\Service\Debt\Form;

use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Service\Transaction\Transaction\Form\TransactionPartDataInterface;
use DateTimeInterface;

class DebtData implements TransactionPartDataInterface
{
    private float $amount;

    private DateTimeInterface $created;

    private ?DateTimeInterface $edited;

    private Transaction $transaction;

    private ?User $owner;

    private bool $paid;

    private string $reason;

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

    public function setCreated(DateTimeInterface $created): DebtData
    {
        $this->created = $created;
        return $this;
    }

    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    public function setEdited(?DateTimeInterface $edited): DebtData
    {
        $this->edited = $edited;
        return $this;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): DebtData
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): DebtData
    {
        $this->owner = $owner;
        return $this;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): DebtData
    {
        $this->paid = $paid;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): DebtData
    {
        $this->reason = $reason;
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

    public function setInitialAmount(float $initialAmount): DebtData
    {
        $this->initialAmount = $initialAmount;
        return $this;
    }

    public function initFrom(TransactionPartInterface $debt): DebtData
    {
        $this->setCreated($debt->getCreated());
        $this->setAmount($debt->getAmount());
        $this->setInitialAmount($debt->getInitialAmount());
        $this->setOwner($debt->getOwner());
        $this->setPaid($debt->getPaid());
        $this->setTransaction($debt->getTransaction());
        $this->setEdited($debt->getEdited());
        $this->setState($debt->getState());

        return $this;
    }
}
