<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Payment;

use App\Entity\GroupEvent;
use App\Entity\GroupEventPayment;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
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
