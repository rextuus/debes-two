<?php

namespace App\Service\Loan\Form;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\Transaction\Form\TransactionCreateLegacyImportData;
use App\Service\Transaction\Transaction\Form\TransactionData;
use DateTime;

class LoanCreateData extends LoanData
{

    public function initFromData(TransactionData $data, User $owner): LoanCreateData
    {
        $this->setAmount($data->getAmount());
        $this->setReason($data->getReason());
        $this->setCreated(new DateTime());
        $this->setEdited(new DateTime());
        $this->setOwner($owner);
        $this->setPaid(false);
        $this->setState(Transaction::STATE_READY);

        if ($data instanceof TransactionCreateLegacyImportData){
            $this->setCreated($data->getCreated());
            $this->setState($data->getState());
        }

        return $this;
    }
}