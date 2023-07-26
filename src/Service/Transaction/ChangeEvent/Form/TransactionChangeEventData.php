<?php

namespace App\Service\Transaction\ChangeEvent\Form;

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

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): TransactionChangeEventData
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getOldState(): string
    {
        return $this->oldState;
    }

    public function setOldState(string $oldState): TransactionChangeEventData
    {
        $this->oldState = $oldState;
        return $this;
    }

    public function getNewState(): string
    {
        return $this->newState;
    }

    public function setNewState(string $newState): TransactionChangeEventData
    {
        $this->newState = $newState;
        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): TransactionChangeEventData
    {
        $this->created = $created;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): TransactionChangeEventData
    {
        $this->type = $type;
        return $this;
    }

    public function getPaymentTarget(): ?TransactionStateChangeTargetInterface
    {
        return $this->paymentTarget;
    }

    public function setPaymentTarget(?TransactionStateChangeTargetInterface $paymentTarget): TransactionChangeEventData
    {
        $this->paymentTarget = $paymentTarget;
        return $this;
    }

    public function getExchangeTarget(): ?TransactionStateChangeTargetInterface
    {
        return $this->exchangeTarget;
    }

    public function setExchangeTarget(?TransactionStateChangeTargetInterface $exchangeTarget
    ): TransactionChangeEventData {
        $this->exchangeTarget = $exchangeTarget;
        return $this;
    }

    public function getTarget(): ?TransactionStateChangeTargetInterface
    {
        return $this->target;
    }

    public function setTarget(?TransactionStateChangeTargetInterface $target): TransactionChangeEventData
    {
        $this->target = $target;
        return $this;
    }
}