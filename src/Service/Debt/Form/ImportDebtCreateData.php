<?php

namespace App\Service\Debt\Form;

use App\Entity\User;
use App\Service\Transaction\Transaction\Form\TransactionData;

class ImportDebtCreateData extends DebtCreateData
{
    public function initFromUser(User $debtor): ImportDebtCreateData
    {
        $this->setOwner($debtor);
        return $this;
    }

    public function initFromData(TransactionData $data): ImportDebtCreateData
    {
        parent::initFromData($data);
        $this->setCreated($data->getCreated());
        $this->setState($data->getState());

        return $this;
    }
}