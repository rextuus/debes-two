<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Event;

use App\Entity\GroupEvent;
use App\Service\GroupEvent\Event\Form\GroupEventData;
use App\Service\GroupEvent\Event\Form\GroupEventInitData;
use DateTime;


class GroupEventFactory
{
    public function createByData(GroupEventData $groupEventData): GroupEvent
    {
        $groupEvent = $this->createNewGroupEventInstance();
        $this->mapData($groupEvent, $groupEventData);

        return $groupEvent;
    }

    public function mapData(GroupEvent $groupEvent, GroupEventData $groupEventData): void
    {
        if ($groupEventData instanceof GroupEventInitData){
            $groupEvent->setCreated(new DateTime());
            $groupEvent->setCreator($groupEventData->getCreator());
        }

        $groupEvent->setOpen($groupEventData->isOpen());
        $groupEvent->setDescription($groupEventData->getDescription());
        $groupEvent->setEvaluated($groupEventData->getEvaluated());
    }

    private function createNewGroupEventInstance(): GroupEvent
    {
        return new GroupEvent();
    }
}
