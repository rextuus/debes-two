<?php

namespace App\Service\Transaction\ChangeEvent;

use App\Entity\Transaction;
use App\Service\Transaction\TransactionStateChangeTargetInterface;
use DateTime;

class TransactionChangeEventData
{
    private Transaction $transaction;
    private string $oldState;
    private string $newState;
    private DateTime $created;
    private ?string $type;
    private ?TransactionStateChangeTargetInterface $paymentTarget;
    private ?TransactionStateChangeTargetInterface $exchangeTarget;
    private ?TransactionStateChangeTargetInterface $target = null;

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @return string
     */
    public function getOldState(): string
    {
        return $this->oldState;
    }

    /**
     * @param string $oldState
     */
    public function setOldState(string $oldState): void
    {
        $this->oldState = $oldState;
    }

    /**
     * @return string
     */
    public function getNewState(): string
    {
        return $this->newState;
    }

    /**
     * @param string $newState
     */
    public function setNewState(string $newState): void
    {
        $this->newState = $newState;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return TransactionStateChangeTargetInterface|null
     */
    public function getPaymentTarget(): ?TransactionStateChangeTargetInterface
    {
        return $this->paymentTarget;
    }

    /**
     * @param TransactionStateChangeTargetInterface|null $paymentTarget
     */
    public function setPaymentTarget(?TransactionStateChangeTargetInterface $paymentTarget): void
    {
        $this->paymentTarget = $paymentTarget;
    }

    /**
     * @return TransactionStateChangeTargetInterface|null
     */
    public function getExchangeTarget(): ?TransactionStateChangeTargetInterface
    {
        return $this->exchangeTarget;
    }

    /**
     * @param TransactionStateChangeTargetInterface|null $exchangeTarget
     */
    public function setExchangeTarget(?TransactionStateChangeTargetInterface $exchangeTarget): void
    {
        $this->exchangeTarget = $exchangeTarget;
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