<?php

namespace App\Service\Transaction\Transaction\Form;

class TransactionCreateData extends TransactionData
{
    private int$debtors;

    private int $loaners;

    public function getDebtors(): int
    {
        return $this->debtors;
    }

    public function setDebtors(int $debtors): TransactionCreateData
    {
        $this->debtors = $debtors;
        return $this;
    }

    public function getLoaners(): int
    {
        return $this->loaners;
    }

    public function setLoaners(int $loaners): TransactionCreateData
    {
        $this->loaners = $loaners;
        return $this;
    }
}
