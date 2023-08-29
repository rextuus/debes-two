<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\GroupEvent;
use App\Entity\GroupEventResult;
use App\Entity\GroupEventUserCollection;
use App\Entity\User;
use App\Service\GroupEvent\Calculation\CalculationTransactionDto;
use App\Service\GroupEvent\Calculation\CalculationUserDto;
use App\Service\GroupEvent\Calculation\GroupEventCalculator;
use App\Service\GroupEvent\Event\Form\GroupEventData;
use App\Service\GroupEvent\Event\Form\GroupEventInitData;
use App\Service\GroupEvent\Event\GroupEventService;
use App\Service\GroupEvent\Payment\Form\GroupEventPaymentData;
use App\Service\GroupEvent\Payment\GroupEventPaymentService;
use App\Service\GroupEvent\Result\GroupEventResultService;
use App\Service\GroupEvent\UserCollection\Form\UserCollectionData;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionService;
use App\Service\GroupEvent\UserCollection\UserCollectionDto;

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
        $groupsToCreate = $this->calculateUserGroups($groupEventData->getSelectedUsers());

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

    public function triggerTransactionCreation(GroupEvent $event, bool $createExchanges = true): void
    {
        $this->calculator->triggerTransactionCreation($event, $createExchanges);
    }

    /**
     * @return User[][]
     */
    private function calculateUserGroups(array $users): array
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
     * @param User[] $users
     * @return User[]
     */
    private function calculateUserCombinations(array $users): array
    {
        $pairs = [];
        foreach ($users as $userNr => $userToCheck) {
//            if ($userNr === count($users) - 1) {
//                break;
//            }
//            $pairs[$userToCheck->getId()] = [];
//            for ($userNr2 = $userNr + 1; $userNr2 < count($users); $userNr2++) {
//                $pairs[$userToCheck->getId()][] = $users[$userNr2];
//            }
            $pairs[$userToCheck->getId()] = [];
            foreach ($users as $userToCheck2){
                if ($userToCheck !== $userToCheck2){
                    $pairs[$userToCheck->getId()][] = $userToCheck2;
                }
            }
        }
        return $pairs;
    }

    /**
     * @return UserCollectionDto[]
     */
    public function getResultsDtosForEvent(GroupEvent $event): array
    {
        $users = $event->getUsers();
        $userCombinations = $this->calculateUserCombinations($users);

        $dtos = [];
        $index = 0;
        foreach ($userCombinations as $userId => $otherUsers){
            $user = $users[$index];
            $index++;
            $userDto = new CalculationUserDto();
            $userDto->setLoaner($user);

            $transactions = [];
            foreach ($otherUsers as $partner) {
                $to = $this->resultService->findByEventDebtorLoanerCombination($event, $user, $partner);
                $from = $this->resultService->findByEventDebtorLoanerCombination($event, $partner, $user);
                $transactionDto = new CalculationTransactionDto();
                $transactionDto->setAmount(!is_null($to) ? $to->getAmount() : 0.0);
                $transactionDto->setAmountReturn(!is_null($from) ? $from->getAmount() : 0.0);
                $transactionDto->setHiddenClassTo(!is_null($to) ? '' : 'ge-result-tile-invisible');
                $transactionDto->setHiddenClassFrom(!is_null($from) ? '' : 'ge-result-tile-invisible');
                $transactionDto->setDebtor($partner);
                $transactions[] = $transactionDto;
            }
            $userDto->setTransactions($transactions);
            $dtos[] = $userDto;
        }
        return $dtos;
    }
}
