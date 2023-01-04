<?php

namespace App\Service\Transaction;

use App\Entity\Transaction;
use DateTime;
use DateTimeInterface;

class TransactionUpdateData extends TransactionData
{
    private DateTime $created;
    private ?DateTimeInterface $edited;
    private string $state;
    private ?string $changeType = null;
    private ?TransactionStateChangeTargetInterface $target = null;

    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    public function setEdited(?DateTimeInterface $edited): void
    {
        $this->edited = $edited;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getChangeType(): ?string
    {
        return $this->changeType;
    }

    public function setChangeType(?string $changeType): void
    {
        $this->changeType = $changeType;
    }

    public function initFrom(Transaction $transaction): TransactionUpdateData
    {
        $this->setReason($transaction->getReason());
        $this->setState($transaction->getState());
        $this->setAmount($transaction->getAmount());
        $this->setDebts($transaction->getDebts());
        $this->setLoans($transaction->getLoans());
        $this->setCreated($transaction->getCreated());
        $this->setEdited($transaction->getEdited());
        return $this;
    }

    /**
     * @return TransactionStateChangeTargetInterface|null
     */
    public function getTarget(): ?TransactionStateChangeTargetInterface
    {
        return $this->target;
    }

    /**
     * @param TransactionStateChangeTargetInterface|null $target
     */
    public function setTarget(?TransactionStateChangeTargetInterface $target): void
    {
        $this->target = $target;
    }
}
