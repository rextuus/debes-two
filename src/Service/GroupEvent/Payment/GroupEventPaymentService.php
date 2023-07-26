<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Payment;

use App\Entity\GroupEventPayment;
use App\Service\GroupEvent\Payment\Form\GroupEventPaymentData;

class GroupEventPaymentService
{
    public function __construct(
        private GroupEventPaymentFactory    $groupEventPaymentFactory,
        private GroupEventPaymentRepository $groupEventPaymentRepository,
    )
    {
    }

    public function storeGroupEventPayment(GroupEventPaymentData $groupEventPaymentData, bool $persist = true): GroupEventPayment
    {
        $groupEventPayment = $this->groupEventPaymentFactory->createByData($groupEventPaymentData);

        if ($persist) {
            $this->groupEventPaymentRepository->persist($groupEventPayment);
        }

        return $groupEventPayment;
    }

    public function update(GroupEventPayment $groupEventPayment, GroupEventPaymentData $data): void
    {
        $this->groupEventPaymentFactory->mapData($groupEventPayment, $data);

        $this->groupEventPaymentRepository->persist($groupEventPayment);
    }


}
