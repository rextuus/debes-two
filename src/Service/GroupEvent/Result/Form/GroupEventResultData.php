<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Result\Form;

use App\Entity\GroupEvent;
use App\Entity\GroupEventResult;
use App\Entity\User;

class GroupEventResultData
{
    private GroupEvent $event;
    private User $loaner;
    private User $debtor;
    private float $amount;
    private string $reason;

    public function getEvent(): GroupEvent
    {
        return $this->event;
    }

    public function setEvent(GroupEvent $event): GroupEventResultData
    {
        $this->event = $event;
        return $this;
    }

    public function getLoaner(): User
    {
        return $this->loaner;
    }

    public function setLoaner(User $loaner): GroupEventResultData
    {
        $this->loaner = $loaner;
        return $this;
    }

    public function getDebtor(): User
    {
        return $this->debtor;
    }

    public function setDebtor(User $debtor): GroupEventResultData
    {
        $this->debtor = $debtor;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): GroupEventResultData
    {
        $this->amount = $amount;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): GroupEventResultData
    {
        $this->reason = $reason;
        return $this;
    }

    public function initFrom(GroupEventResult $result): GroupEventResultData
    {
        $this->setEvent($result->getEvent());
        $this->setReason($result->getReason());
        $this->setLoaner($result->getLoaner());
        $this->setDebtor($result->getDebtor());
        $this->setAmount($result->getAmount());

        return $this;
    }
}