<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Service\GroupEvent\Calculation\GroupEventCalculator;
use App\Service\GroupEvent\Payment\GroupEventPaymentData;
use App\Service\GroupEvent\Payment\GroupEventPaymentService;
use App\Service\GroupEvent\Result\GroupEventResultService;
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
        private GroupEventCalculator $groupEventCalculator,
        private GroupEventResultService $groupEventResultService,
    ) {
    }

    public function initEvent(GroupEventData $groupEventData): GroupEvent
    {
        return $this->groupEventService->storeGroupEvent($groupEventData);
    }

    public function addInitialUserCollectionsToGroupEvent(
        GroupEventInitData $groupEventData,
        GroupEvent $event
    ): ?GroupEventUserCollection {
        // TODO check if there already exist the userGroups

        // all members
        $userGroup = $this->groupEventUserCollectionService->getGroupByUserList($groupEventData->getSelectedUsers());
        if (is_null($userGroup)) {
            $data = new GroupEventUserCollectionData();

            $users = $groupEventData->getSelectedUsers();
            $data->setUsers($users);
            $data->setInitial(true);
            $data->setGroupEvent($event);
            $data->setName('Alle');

            $this->addUserCollectionToGroupEventByData($data);
        }else{
            $this->addUserCollectionToGroupEvent($event, $userGroup);
        }

        // all members initiator excluded
        $users = $groupEventData->getSelectedUsers();
        $creator = $groupEventData->getCreator();
        $users = array_filter($users, function ($user) use ($creator) {
            return $user !== $creator;
        });

        $userGroup = $this->groupEventUserCollectionService->getGroupByUserList($users);

        $collection = null;
        if (is_null($userGroup)) {
            $data = new GroupEventUserCollectionData();

            $data->setUsers($users);
            $data->setAllOthers(true);
            $data->setGroupEvent($event);
            $data->setName('Alle außer mir');
            $collection = $this->addUserCollectionToGroupEventByData($data);
        }else{
            $this->addUserCollectionToGroupEvent($event, $userGroup);
        }

        return $collection;
    }

    public function addUserCollectionToGroupEvent(
        GroupEvent $groupEvent,
        GroupEventUserCollection $groupEventUserCollection,
    ): void {
        $this->groupEventService->addGroupToEvent(
            $groupEvent,
            $groupEventUserCollection
        );
    }

    public function addUserCollectionToGroupEventByData(GroupEventUserCollectionData $groupEventUserCollectionData,
    ): GroupEventUserCollection {
        return $this->groupEventUserCollectionService->storeGroupEventUserCollection($groupEventUserCollectionData);
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

    public function getGroupsOfEvent(GroupEvent $groupEvent): ?GroupEventUserCollection
    {
        return $this->groupEventUserCollectionService->getGroupsByEvent($groupEvent);
    }

    public function calculateGroupEventFinalBill(GroupEvent $event)
    {
        $this->groupEventCalculator->calculateGroupEventFinalBill($event);
    }

    public function triggerTransactionCreation(GroupEvent $event):void{
        $this->groupEventCalculator->triggerTransactionCreation($event);
    }
}
