<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Event;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Service\GroupEvent\Event\Form\GroupEventData;


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

    public function addGroupToEvent(
        GroupEvent $groupEvent,
        GroupEventUserCollection $groupEventUserCollection
    ) {
        $groupEvent->addParticipantGroup($groupEventUserCollection);
        $this->groupEventRepository->persist($groupEvent);
    }
}
