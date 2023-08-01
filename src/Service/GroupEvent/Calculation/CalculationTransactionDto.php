<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Calculation;

use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class CalculationTransactionDto
{
    private User $debtor;
    private float|null $amount;
    private float|null $amountReturn;
    private string $colorClass;
    private string $hiddenClassTo;
    private string $hiddenClassFrom;

    public function getDebtor(): User
    {
        return $this->debtor;
    }

    public function setDebtor(User $debtor): CalculationTransactionDto
    {
        $this->debtor = $debtor;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): CalculationTransactionDto
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmountReturn(): ?float
    {
        return $this->amountReturn;
    }

    public function setAmountReturn(?float $amountReturn): CalculationTransactionDto
    {
        $this->amountReturn = $amountReturn;
        return $this;
    }

    public function getColorClass(): string
    {
        return $this->colorClass;
    }

    public function setColorClass(string $colorClass): CalculationTransactionDto
    {
        $this->colorClass = $colorClass;
        return $this;
    }

    public function getHiddenReverseClass(): string
    {
        if (is_null($this->amountReturn)) {
            return '';
        }
        return 'ge-result-tile-invisible';
    }

    public function getHiddenClassTo(): string
    {
        return $this->hiddenClassTo;
    }

    public function setHiddenClassTo(string $hiddenClassTo): CalculationTransactionDto
    {
        $this->hiddenClassTo = $hiddenClassTo;
        return $this;
    }

    public function getHiddenClassFrom(): string
    {
        return $this->hiddenClassFrom;
    }

    public function setHiddenClassFrom(string $hiddenClassFrom): CalculationTransactionDto
    {
        $this->hiddenClassFrom = $hiddenClassFrom;
        return $this;
    }
}