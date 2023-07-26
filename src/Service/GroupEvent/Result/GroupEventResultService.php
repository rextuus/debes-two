<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Result;

use App\Entity\GroupEvent;
use App\Entity\GroupEventResult;
use App\Entity\User;
use App\Service\GroupEvent\Result\Form\GroupEventResultData;

class GroupEventResultService
{
    public function __construct(
        private GroupEventResultFactory $groupEventResultFactory,
        private GroupEventResultRepository $groupEventResultRepository,
    ) {
    }

    public function storeGroupEventResult(
        GroupEventResultData $groupEventResultData,
        bool $persist = true
    ): GroupEventResult {
        $groupEventResult = $this->groupEventResultFactory->createByData($groupEventResultData);

        if ($persist) {
            $this->groupEventResultRepository->save($groupEventResult, true);
        }

        return $groupEventResult;
    }

    public function update(GroupEventResult $groupEventResult, GroupEventResultData $data): void
    {
        $this->groupEventResultFactory->mapData($groupEventResult, $data);

        $this->groupEventResultRepository->save($groupEventResult, true);
    }

    /**
     * @return GroupEventResult[]
     */
    public function findAllForEvent(GroupEvent $event): array
    {
        return $this->groupEventResultRepository->findBy(['event' => $event]);
    }

    public function findByEventDebtorLoanerCombination(GroupEvent $event, User $debtor, User $loaner): ?GroupEventResult
    {
        return $this->groupEventResultRepository->findOneBy(['event' => $event, 'debtor' => $debtor, 'loaner' => $loaner]);
    }
}