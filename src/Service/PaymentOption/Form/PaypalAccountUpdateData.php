<?php

namespace App\Service\PaymentOption\Form;

use App\Entity\PaypalAccount;

class PaypalAccountUpdateData extends PaypalAccountData
{

    public function initFromEntity(PaypalAccount $paypalAccount): PaypalAccountData
    {
        $this->setEnabled($paypalAccount->getEnabled());
        $this->setOwner($paypalAccount->getOwner());
        $this->setEmail($paypalAccount->getEmail());
        $this->setPaypalMeLink($paypalAccount->getPaypalMeLink());
        $this->setDescription($paypalAccount->getDescription());
        $this->setPreferred($paypalAccount->getIsPrioritised());
        return $this;
    }
}
