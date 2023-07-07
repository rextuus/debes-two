<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Payment;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventPaymentData
{
    private GroupEvent $groupEvent;
    private float $amount;
    private User $loaner;
    private GroupEventUserCollection $debtors;

    public function getGroupEvent(): GroupEvent
    {
        return $this->groupEvent;
    }

    public function setGroupEvent(GroupEvent $groupEvent): GroupEventPaymentData
    {
        $this->groupEvent = $groupEvent;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): GroupEventPaymentData
    {
        $this->amount = $amount;
        return $this;
    }

    public function getLoaner(): User
    {
        return $this->loaner;
    }

    public function setLoaner(User $loaner): GroupEventPaymentData
    {
        $this->loaner = $loaner;
        return $this;
    }

    public function getDebtors(): GroupEventUserCollection
    {
        return $this->debtors;
    }

    public function setDebtors(GroupEventUserCollection $debtors): GroupEventPaymentData
    {
        $this->debtors = $debtors;
        return $this;
    }
}