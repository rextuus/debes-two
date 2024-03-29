<?php

namespace App\Service\Debt;

use App\Entity\Debt;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Service\Debt\Form\DebtCreateData;
use App\Service\Debt\Form\ImportDebtCreateData;
use App\Service\Transaction\Transaction\Form\TransactionPartDataInterface;

class DebtFactory
{

    public function createByData(DebtCreateData $DebtData): Debt
    {
        $debt = $this->createNewDebtInstance();
        $this->mapData($debt, $DebtData);

        return $debt;
    }


    public function mapData(TransactionPartInterface $debt, TransactionPartDataInterface $data): void
    {
        if ($data instanceof ImportDebtCreateData) {
            $debt->setCreated($data->getCreated());
            $debt->setEdited($data->getEdited());
            $debt->setState($data->getState());
            $debt->setInitialAmount($data->getAmount());
        } elseif ($data instanceof DebtCreateData) {
            $debt->setCreated($data->getCreated());
            $debt->setEdited($data->getCreated());
            $debt->setState(Transaction::STATE_READY);
            $debt->setInitialAmount($data->getAmount());
        } else {
            $debt->setEdited($data->getEdited());
            $debt->setState($data->getState());
            $debt->setAmount($data->getAmount());
        }

        $debt->setAmount($data->getAmount());
        $debt->setOwner($data->getOwner());
        $debt->setTransaction($data->getTransaction());
        $debt->setPaid($data->isPaid());
    }

    private function createNewDebtInstance(): Debt
    {
        return new Debt();
    }
}