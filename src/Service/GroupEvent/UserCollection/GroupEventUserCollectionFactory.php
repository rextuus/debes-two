<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\UserCollection;

use App\Entity\GroupEventUserCollection;
use App\Service\GroupEvent\UserCollection\Form\UserCollectionData;

class GroupEventUserCollectionFactory
{
    public function createByData(UserCollectionData $data): GroupEventUserCollection
    {
        $userCollection = $this->createNewGroupEventUserCollectionInstance();
        $this->mapData($userCollection, $data);

        return $userCollection;
    }

    public function mapData(GroupEventUserCollection $userCollection, UserCollectionData $data): void
    {
        foreach ($data->getUsers() as $user){
            $userCollection->addUser($user);
        }
        $userCollection->setInitial($data->isInitial());
        $userCollection->setAllOther($data->isAllOthers());
        $userCollection->setName($data->getName());
        $userCollection->setEvent($data->getGroupEvent());
        $userCollection->getEvent()->getUserGroups()->add($userCollection);
    }

    private function createNewGroupEventUserCollectionInstance(): GroupEventUserCollection
    {
        return new GroupEventUserCollection();
    }
}
