<?php

namespace App\Service\Exchange\Form;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;

class ExchangeData
{
    private float $remainingAmount;

    private Transaction $transaction;

    private float $amount;

    private Loan $loan;

    private Debt $debt;

    public function getRemainingAmount(): float
    {
        return $this->remainingAmount;
    }

    public function setRemainingAmount(float $remainingAmount): ExchangeData
    {
        $this->remainingAmount = $remainingAmount;
        return $this;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): ExchangeData
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): ExchangeData
    {
        $this->amount = $amount;
        return $this;
    }

    public function getLoan(): Loan
    {
        return $this->loan;
    }

    public function setLoan(Loan $loan): ExchangeData
    {
        $this->loan = $loan;
        return $this;
    }

    public function getDebt(): Debt
    {
        return $this->debt;
    }

    public function setDebt(Debt $debt): ExchangeData
    {
        $this->debt = $debt;
        return $this;
    }
}