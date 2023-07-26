<?php

namespace App\Service\Loan\Form;

use App\Entity\User;
use App\Service\Transaction\Transaction\Form\TransactionData;

class ImportLoanCreateData extends LoanCreateData
{
    public function initFromData(TransactionData $data, User $owner): self
    {
        parent::initFromData($data, $owner);

        $this->setCreated($data->getCreated());
        $this->setState($data->getState());

        return $this;
    }
}