<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\GroupEvent;
use App\Entity\GroupEventResult;
use App\Entity\GroupEventUserCollection;
use App\Entity\User;
use App\Service\GroupEvent\Calculation\GroupEventCalculator;
use App\Service\GroupEvent\Event\Form\GroupEventData;
use App\Service\GroupEvent\Event\Form\GroupEventInitData;
use App\Service\GroupEvent\Event\GroupEventService;
use App\Service\GroupEvent\Payment\Form\GroupEventPaymentData;
use App\Service\GroupEvent\Payment\GroupEventPaymentService;
use App\Service\GroupEvent\Result\GroupEventResultService;
use App\Service\GroupEvent\UserCollection\Form\UserCollectionData;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionService;

class GroupEventManager
{
    public function __construct(
        private readonly GroupEventService $eventService,
        private readonly GroupEventPaymentService $paymentService,
        private readonly GroupEventUserCollectionService $userCollectionService,
        private readonly GroupEventCalculator $calculator,
        private readonly GroupEventResultService $resultService,
    ) {
    }

    public function initEvent(GroupEventData $groupEventData): GroupEvent
    {
        return $this->eventService->storeGroupEvent($groupEventData);
    }

    public function addInitialUserCollectionsToGroupEvent(
        GroupEventInitData $groupEventData,
        GroupEvent $event
    ): void {
        // all members
        $this->addGroupByUsers($groupEventData->getSelectedUsers(), $event);

        // we will create at least one userGroup for each participant, so that he can use predefined "All others" May a bit to much but we have the money bitch
        $groupsToCreate = $this->calculateUserGroupMatrix($groupEventData->getSelectedUsers());

        foreach ($groupsToCreate as $group){
            $this->addGroupByUsers($group, $event, false);
        }

        // all members initiator excluded
//        $users = $groupEventData->getSelectedUsers();
//        $creator = $groupEventData->getCreator();
//        $users = array_filter($users, function ($user) use ($creator) {
//            return $user !== $creator;
//        });
//
//        $this->addGroupByUsers($users, $event, false);
    }

    /**
     * @param User[] $users
     */
    private function addGroupByUsers(array $users, GroupEvent $event, bool $initial = true): void
    {
        $data = new UserCollectionData();

        $data->setUsers($users);
        $data->setGroupEvent($event);
        $data->setInitial($initial);
        $data->setAllOthers(!$initial);
        $name = 'Alle';
        if (!$initial) {
            $names = array_map(
                function (User $user) {
                    return $user->getFirstName();
                },
                $users
            );
            $name = $event->getDescription().': '.implode(',',$names);
        }
        $data->setName($name);

        $this->addUserCollectionToGroupEventByData($data);
    }

    public function addUserCollectionToGroupEvent(
        GroupEvent $groupEvent,
        GroupEventUserCollection $groupEventUserCollection,
    ): void {
        $this->eventService->addGroupToEvent(
            $groupEvent,
            $groupEventUserCollection
        );
    }

    public function addUserCollectionToGroupEventByData(UserCollectionData $groupEventUserCollectionData,
    ): GroupEventUserCollection {
        return $this->userCollectionService->storeGroupEventUserCollection($groupEventUserCollectionData);
    }

    public function addPaymentToEvent(GroupEventPaymentData $groupEventPaymentData)
    {
        return $this->paymentService->storeGroupEventPayment($groupEventPaymentData);
    }

    public function getTotalAmountOfEvent(GroupEvent $groupEvent): float
    {
        return $this->eventService->getTotalAmountOfEvent($groupEvent);
    }

    public function findGroupEvent(int $id): ?GroupEvent
    {
        return $this->eventService->findGroupEvent($id);
    }

    public function getGroupsOfEvent(GroupEvent $groupEvent): ?GroupEventUserCollection
    {
        return $this->userCollectionService->getGroupsByEvent($groupEvent);
    }

    public function calculateGroupEventFinalBill(GroupEvent $event): void
    {
        $this->calculator->calculateGroupEventFinalBill($event);
    }

    public function triggerTransactionCreation(GroupEvent $event, bool $createExchanges = true):void{
        $this->calculator->triggerTransactionCreation($event, $createExchanges);
    }

    /**
     * @return User[][]
     */
    private function calculateUserGroupMatrix(array $users): array
    {
        $groups = [];
        foreach ($users as $userToCheck) {
            $groups[] = array_filter(
                $users,
                function (User $user) use ($userToCheck) {
                    if ($user === $userToCheck) {
                        return false;
                    }
                    return true;
                }
            );
        }
        return $groups;
    }


    /**
     * @return GroupEventResult[]
     */
    public function getResultsForEvent(GroupEvent $event): array
    {
        return $this->resultService->findAllForEvent($event);
    }
}
