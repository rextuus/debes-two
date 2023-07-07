<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\UserCollection;

use App\Entity\GroupEvent;
use App\Entity\GroupEventPayment;
use App\Entity\GroupEventUserCollection;
use App\Service\GroupEvent\GroupEventData;
use App\Service\GroupEvent\GroupEventInitData;
use App\Service\GroupEvent\Payment\GroupEventPaymentData;
use DateTime;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventUserCollectionFactory
{
    public function createByData(GroupEventUserCollectionData $groupEventUserCollectionData): GroupEventUserCollection
    {
        $groupEventUserCollection = $this->createNewGroupEventUserCollectionInstance();
        $this->mapData($groupEventUserCollection, $groupEventUserCollectionData);

        return $groupEventUserCollection;
    }

    public function mapData(GroupEventUserCollection $groupEventUserCollection, GroupEventUserCollectionData $groupEventUserCollectionData): void
    {
        foreach ($groupEventUserCollectionData->getUsers() as $user){
            $groupEventUserCollection->addUser($user);
        }
        $groupEventUserCollection->setInitial($groupEventUserCollectionData->isInitial());
    }

    private function createNewGroupEventUserCollectionInstance(): GroupEventUserCollection
    {
        return new GroupEventUserCollection();
    }
}
