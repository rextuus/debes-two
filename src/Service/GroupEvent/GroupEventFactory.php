<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\GroupEvent;
use DateTime;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventFactory
{
    public function createByData(GroupEventData $groupEventData): GroupEvent
    {
        $groupEvent = $this->createNewGroupEventInstance();
        $this->mapData($groupEvent, $groupEventData);

        return $groupEvent;
    }

    public function mapData(GroupEvent $groupEvent, GroupEventData $groupEventData)
    {
        if ($groupEventData instanceof GroupEventInitData){
            $groupEvent->setCreated(new DateTime());
            $groupEvent->setCreator($groupEventData->getCreator());
        }

        $groupEvent->setDecscription($groupEventData->getDescription());
    }

    private function createNewGroupEventInstance(): GroupEvent
    {
        return new GroupEvent();
    }
}
