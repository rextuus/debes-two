<?php

namespace App\Service\Transaction\ChangeEvent;

use App\Entity\Exchange;
use App\Entity\PaymentAction;
use App\Entity\TransactionStateChangeEvent;
use DateTime;

class TransactionChangeEventFactory
{
    public function createByData(TransactionChangeEventData $changeEventData): TransactionStateChangeEvent
    {
        $paymentOption = $this->createNewPaymentOptionInstance();

        $this->mapData($paymentOption, $changeEventData);
        return $paymentOption;
    }

    public function mapData(TransactionStateChangeEvent $changeEvent, TransactionChangeEventData $data): void
    {
        $changeEvent->setTransaction($data->getTransaction());
        $changeEvent->setCreated(new DateTime());
        $changeEvent->setOldState($data->getOldState());
        $changeEvent->setNewState($data->getNewState());
        $changeEvent->setType($data->getType());

        if ($data->getTarget()) {
            $target = $data->getTarget();
            if ($target instanceof Exchange) {
                $changeEvent->setExchangeTarget($target);
            }
            if ($target instanceof PaymentAction) {
                $changeEvent->setPaymentTarget($target);
            }
        }
    }

    public function createNewPaymentOptionInstance(): TransactionStateChangeEvent
    {
        return new TransactionStateChangeEvent();
    }
}