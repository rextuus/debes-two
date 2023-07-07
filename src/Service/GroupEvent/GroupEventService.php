<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\GroupEvent;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventService
{
    public function __construct(
        private GroupEventFactory    $groupEventFactory,
        private GroupEventRepository $groupEventRepository,
    )
    {
    }

    public function storeGroupEvent(GroupEventData $groupEventData, bool $persist = true): GroupEvent
    {
        $groupEvent = $this->groupEventFactory->createByData($groupEventData);

        if ($persist) {
            $this->groupEventRepository->persist($groupEvent);
        }

        return $groupEvent;
    }

    public function update(GroupEvent $groupEvent, GroupEventData $data): void
    {
        $this->groupEventFactory->mapData($groupEvent, $data);

        $this->groupEventRepository->persist($groupEvent);
    }

    public function getTotalAmountOfEvent(GroupEvent $groupEvent): float
    {
        return $this->groupEventRepository->getTotalSumOfEvent($groupEvent);
    }

    public function findGroupEvent(int $id): ?GroupEvent
    {
        return $this->groupEventRepository->find($id);
    }
}
