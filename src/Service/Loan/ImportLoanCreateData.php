<?php

namespace App\Service\Loan;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\TransactionCreateLegacyImportData;
use App\Service\Transaction\TransactionData;
use DateTime;

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