<?php

namespace App\Service\Transaction;

enum TransactionVariant
{
    case DEBT;
    case LOAN;

    public function isDebtVariant(): bool
    {
        return match($this)
        {
            TransactionVariant::DEBT => true,
            TransactionVariant::LOAN => false,
        };
    }
}
