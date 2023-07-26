<?php

namespace App\Service\PaymentOption\Form;

use App\Entity\BankAccount;

class BankAccountUpdateData extends BankAccountData
{

    public function initFromEntity(BankAccount $bankAccount): BankAccountData
    {
        $this->setEnabled($bankAccount->getEnabled());
        $this->setOwner($bankAccount->getOwner());
        $this->setIban($bankAccount->getIban());
        $this->setBic($bankAccount->getBic());
        $this->setAccountName($bankAccount->getAccountName());
        $this->setBankName($bankAccount->getBankName());
        $this->setDescription($bankAccount->getDescription());
        $this->setPreferred($bankAccount->getIsPrioritised());
        return $this;
    }
}
