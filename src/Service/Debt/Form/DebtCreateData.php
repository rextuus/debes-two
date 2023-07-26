<?php

namespace App\Service\Debt\Form;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\Transaction\Form\TransactionCreateLegacyImportData;
use App\Service\Transaction\Transaction\Form\TransactionData;
use DateTime;

class DebtCreateData extends DebtData
{

    public function initFromUser(User $debtor): DebtCreateData
    {
        $this->setOwner($debtor);
        return $this;
    }

    public function initFromData(TransactionData $data): DebtCreateData
    {
        $this->setOwner($data->getOwner());
        $this->setAmount($data->getAmount());
        $this->setReason($data->getReason());
        $this->setCreated(new DateTime());
        $this->setEdited(new DateTime());
        $this->setState(Transaction::STATE_READY);
        $this->setPaid(false);
        if ($data instanceof TransactionCreateLegacyImportData) {
            $this->setCreated($data->getCreated());
            $this->setState($data->getState());
        }

        return $this;
    }
}