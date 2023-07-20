<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\UserCollection;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventUserCollectionService
{
    public function __construct(
        private GroupEventUserCollectionFactory $groupEventUserCollectionFactory,
        private GroupEventUserCollectionRepository $groupEventUserCollectionRepository,
    ) {
    }

    public function storeGroupEventUserCollection(
        GroupEventUserCollectionData $groupEventUserCollectionData,
        bool $persist = true
    ): GroupEventUserCollection {
        $groupEventUserCollection = $this->groupEventUserCollectionFactory->createByData($groupEventUserCollectionData);

        if ($persist) {
            $this->groupEventUserCollectionRepository->persist($groupEventUserCollection);
        }

        return $groupEventUserCollection;
    }

    public function update(GroupEventUserCollection $groupEventPayment, GroupEventUserCollectionData $data): void
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
