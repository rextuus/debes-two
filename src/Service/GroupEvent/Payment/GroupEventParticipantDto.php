<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Payment;

class GroupEventParticipantDto
{
    private string $name;
    private float $amount;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GroupEventParticipantDto
    {
        $this->name = $name;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): GroupEventParticipantDto
    {
        $this->amount = $amount;
        return $this;
    }
}
