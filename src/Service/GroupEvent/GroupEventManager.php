<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Service\GroupEvent\Payment\GroupEventPaymentData;
use App\Service\GroupEvent\Payment\GroupEventPaymentService;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionData;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionService;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventManager
{
    public function __construct(
        private GroupEventService $groupEventService,
        private GroupEventPaymentService $groupEventPaymentService,
        private GroupEventUserCollectionService $groupEventUserCollectionService,
    ) {
    }

    public function initEvent(GroupEventData $groupEventData): GroupEvent
    {
        return $this->groupEventService->storeGroupEvent($groupEventData);
    }

    public function addUserCollectionToGroupEvent(GroupEventUserCollectionData $groupEventUserCollectionData,
    ): GroupEventUserCollection {
        return $this->groupEventUserCollectionService->storeGroupEventPayment($groupEventUserCollectionData);
    }

    public function addPaymentToEvent(GroupEventPaymentData $groupEventPaymentData)
    {
        return $this->groupEventPaymentService->storeGroupEventPayment($groupEventPaymentData);
    }

    public function getTotalAmountOfEvent(GroupEvent $groupEvent): float
    {
        return $this->groupEventService->getTotalAmountOfEvent($groupEvent);
    }

    public function findGroupEvent(int $id): ?GroupEvent
    {
        return $this->groupEventService->findGroupEvent($id);
    }
}
