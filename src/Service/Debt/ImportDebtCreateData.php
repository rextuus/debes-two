<?php

namespace App\Service\Debt;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\TransactionCreateLegacyImportData;
use App\Service\Transaction\TransactionData;
use DateTime;

class ImportDebtCreateData extends DebtCreateData
{

    /**
     * initFromUser
     *
     * @param User $debtor
     *
     * @return ImportDebtCreateData
     */
    public function initFromUser(User $debtor): self
    {
        $this->setOwner($debtor);
        return $this;
    }

    /**
     * initFromData
     *
     * @param TransactionData $data
     *
     * @return $this
     */
    public function initFromData(TransactionData $data): self
    {
        parent::initFromData($data);
        $this->setCreated($data->getCreated());
        $this->setState($data->getState());

        return $this;
    }
}