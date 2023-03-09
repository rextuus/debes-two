<?php

namespace App\Service\Transfer;

use App\Entity\Transaction;
use DateTimeInterface;

/**
 * ExchangeDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeDto
{
    private string $reason;

    private string $reasonExchange;

    private DateTimeInterface $created;

    private DateTimeInterface $createdExchange;

    private string $debtor;

    private string $loaner;

    private float $difference;

    private float $amount;

    private float $amountExchange;

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): ExchangeDto
    {
        $this->reason = $reason;
        return $this;
    }

    public function getReasonExchange(): string
    {
        return $this->reasonExchange;
    }

    public function setReasonExchange(string $reasonExchange): ExchangeDto
    {
        $this->reasonExchange = $reasonExchange;
        return $this;
    }

    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): ExchangeDto
    {
        $this->created = $created;
        return $this;
    }

    public function getCreatedExchange(): DateTimeInterface
    {
        return $this->createdExchange;
    }

    public function setCreatedExchange(DateTimeInterface $createdExchange): ExchangeDto
    {
        $this->createdExchange = $createdExchange;
        return $this;
    }

    public function getDebtor(): string
    {
        return $this->debtor;
    }

    public function setDebtor(string $debtor): ExchangeDto
    {
        $this->debtor = $debtor;
        return $this;
    }

    public function getLoaner(): string
    {
        return $this->loaner;
    }

    public function setLoaner(string $loaner): ExchangeDto
    {
        $this->loaner = $loaner;
        return $this;
    }

    public function getDifference(): float
    {
        return $this->difference;
    }

    public function setDifference(float $difference): ExchangeDto
    {
        $this->difference = $difference;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): ExchangeDto
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmountExchange(): float
    {
        return $this->amountExchange;
    }

    public function setAmountExchange(float $amountExchange): ExchangeDto
    {
        $this->amountExchange = $amountExchange;
        return $this;
    }

    public function initFromTransactions(Transaction $debtTransaction, Transaction $loanTransaction): ExchangeDto
    {
        $this->setReason($debtTransaction->getReason());
        $this->setReasonExchange($loanTransaction->getReason());
        $this->setCreated($debtTransaction->getCreated());
        $this->setCreatedExchange($loanTransaction->getCreated());
        $this->setDebtor($debtTransaction->getLoaner()->getFullName());
        $this->setLoaner($loanTransaction->getLoaner()->getFullName());
        $this->setAmount($debtTransaction->getAmount());
        $this->setAmountExchange($loanTransaction->getAmount());
        return $this;
    }
}
