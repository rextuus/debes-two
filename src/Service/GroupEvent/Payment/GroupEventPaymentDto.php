<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Payment;

class GroupEventPaymentDto
{
    private string $loaner;
    private string $reason;
    private string $amount;
    private string $splitAmount;

    /**
     * @var string[]
     */
    private array $members;

    private string $groupName;

    private string $groupColorClass;

    public function getLoaner(): string
    {
        return $this->loaner;
    }

    public function setLoaner(string $loaner): GroupEventPaymentDto
    {
        $this->loaner = $loaner;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): GroupEventPaymentDto
    {
        $this->reason = $reason;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): GroupEventPaymentDto
    {
        $this->amount = $amount;
        return $this;
    }

    public function getSplitAmount(): string
    {
        return $this->splitAmount;
    }

    public function setSplitAmount(string $splitAmount): GroupEventPaymentDto
    {
        $this->splitAmount = $splitAmount;
        return $this;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function setMembers(array $members): GroupEventPaymentDto
    {
        $this->members = $members;
        return $this;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): GroupEventPaymentDto
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getGroupColorClass(): string
    {
        return $this->groupColorClass;
    }

    public function setGroupColorClass(string $groupColorClass): GroupEventPaymentDto
    {
        $this->groupColorClass = $groupColorClass;
        return $this;
    }
}
