<?php

namespace App\Service\PaymentOption;

class PaypalAccountData extends PaymentOptionData
{

    private string $email;

    private ?string $paypalMeLink;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPaypalMeLink(): ?string
    {
        return $this->paypalMeLink;
    }

    public function setPaypalMeLink(?string $paypalMeLink): void
    {
        $this->paypalMeLink = $paypalMeLink;
    }
}
