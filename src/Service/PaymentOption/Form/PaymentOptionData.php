<?php

namespace App\Service\PaymentOption\Form;

use App\Entity\User;

abstract class PaymentOptionData
{

    private User $owner;

    private bool $enabled;

    private bool $preferred;

    private string $description;

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): PaymentOptionData
    {
        $this->owner = $owner;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): PaymentOptionData
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isPreferred(): bool
    {
        return $this->preferred;
    }

    public function setPreferred(bool $preferred): PaymentOptionData
    {
        $this->preferred = $preferred;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): PaymentOptionData
    {
        $this->description = $description;
        return $this;
    }

    public function initFromUser(User $owner): PaymentOptionData
    {
        $this->setOwner($owner);

        return $this;
    }
}
