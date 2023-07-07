<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Payment;

use App\Entity\GroupEventPayment;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventPaymentFactory
{
    public function createByData(GroupEventPaymentData $groupEventPaymentData): GroupEventPayment
    {
        $groupEventPayment = $this->createNewGroupEventPaymentInstance();
        $this->mapData($groupEventPayment, $groupEventPaymentData);

        return $groupEventPayment;
    }

    public function mapData(GroupEventPayment $groupEventPayment, GroupEventPaymentData $groupEventPaymentData): void
    {
        $groupEventPayment->setGroupEvent($groupEventPaymentData->getGroupEvent());
        $groupEventPaymentData->getGroupEvent()->getPayments()->add($groupEventPayment);
        $groupEventPayment->setAmount($groupEventPaymentData->getAmount());
        $groupEventPayment->setLoaner($groupEventPaymentData->getLoaner());
        $groupEventPayment->setDebtors($groupEventPaymentData->getDebtors());
    }

    private function createNewGroupEventPaymentInstance(): GroupEventPayment
    {
        return new GroupEventPayment();
    }
}
