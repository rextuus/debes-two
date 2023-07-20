<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Calculation;

use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class Payment
{
    private User $loaner;
    private User $debtor;
    private float $amount;

    private string $reason;

    public function getLoaner(): User
    {
        return $this->loaner;
    }

    public function setLoaner(User $loaner): Payment
    {
        $this->loaner = $loaner;
        return $this;
    }

    public function getDebtor(): User
    {
        return $this->debtor;
    }

    public function setDebtor(User $debtor): Payment
    {
        $this->debtor = $debtor;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): Payment
    {
        $this->amount = $amount;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): Payment
    {
        $this->reason = $reason;
        return $this;
    }
}