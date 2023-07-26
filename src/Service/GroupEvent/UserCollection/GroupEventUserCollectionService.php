<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\UserCollection;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Service\GroupEvent\UserCollection\Form\UserCollectionData;

class GroupEventUserCollectionService
{
    public function __construct(
        private GroupEventUserCollectionFactory $groupEventUserCollectionFactory,
        private GroupEventUserCollectionRepository $groupEventUserCollectionRepository,
    ) {
    }

    public function storeGroupEventUserCollection(
        UserCollectionData $groupEventUserCollectionData,
        bool $persist = true
    ): GroupEventUserCollection {
        $groupEventUserCollection = $this->groupEventUserCollectionFactory->createByData($groupEventUserCollectionData);

        if ($persist) {
            $this->groupEventUserCollectionRepository->persist($groupEventUserCollection);
        }

        return $groupEventUserCollection;
    }

    public function update(GroupEventUserCollection $groupEventPayment, UserCollectionData $data): void
    {
        $this->groupEventUserCollectionFactory->mapData($groupEventPayment, $data);

        $this->groupEventUserCollectionRepository->persist($groupEventPayment);
    }

    public function getGroupsByEvent(GroupEvent $groupEvent): ?GroupEventUserCollection
    {
        return $this->groupEventUserCollectionRepository->find(['groupEvent' => $groupEvent]);
    }

    public function getGroupByUserList(array $users)
    {
        return $this->groupEventUserCollectionRepository->getGroupByUserList($users);
    }
}
