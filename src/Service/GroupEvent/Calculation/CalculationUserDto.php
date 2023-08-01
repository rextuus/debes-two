<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Calculation;

use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class CalculationUserDto
{
    private User $loaner;

    /**
     * @var CalculationTransactionDto[]
     */
    private array $transactions;
    private string $colorClass;

    public function getLoaner(): User
    {
        return $this->loaner;
    }

    public function setLoaner(User $loaner): CalculationUserDto
    {
        $this->loaner = $loaner;
        return $this;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function setTransactions(array $transactions): CalculationUserDto
    {
        $this->transactions = $transactions;
        return $this;
    }

    public function getColorClass(): string
    {
        return $this->colorClass;
    }

    public function setColorClass(string $colorClass): CalculationUserDto
    {
        $this->colorClass = $colorClass;
        return $this;
    }
}