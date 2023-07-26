<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Result;

use App\Entity\GroupEventResult;
use App\Service\GroupEvent\Result\Form\GroupEventResultData;

class GroupEventResultFactory
{
    public function createByData(GroupEventResultData $groupEventResultData): GroupEventResult
    {
        $groupEventResult = $this->createNewGroupEventResultInstance();
        $this->mapData($groupEventResult, $groupEventResultData);

        return $groupEventResult;
    }

    public function mapData(GroupEventResult $groupEventResult, GroupEventResultData $groupEventResultData): void
    {
        $groupEventResult->setEvent($groupEventResultData->getEvent());
        $groupEventResult->setDebtor($groupEventResultData->getDebtor());
        $groupEventResult->setLoaner($groupEventResultData->getLoaner());
        $groupEventResult->setReason($groupEventResultData->getReason());
        $groupEventResult->setAmount($groupEventResultData->getAmount());
    }

    private function createNewGroupEventResultInstance(): GroupEventResult
    {
        return new GroupEventResult();
    }
}