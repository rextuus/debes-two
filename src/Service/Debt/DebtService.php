<?php

namespace App\Service\Debt;

use App\Entity\Debt;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Repository\DebtRepository;
use App\Service\Transaction\TransactionPartDataInterface;

class DebtService
{
    public function __construct(
        private DebtFactory    $debtFactory,
        private DebtRepository $debtRepository
    )
    {
    }

    public function storeDebt(DebtCreateData $debtData): Debt
    {
        $debt = $this->debtFactory->createByData($debtData);

        $this->debtRepository->persist($debt);

        return $debt;
    }

    public function update(TransactionPartInterface $debt, TransactionPartDataInterface $data): void
    {
        $this->debtFactory->mapData($debt, $data);

        $this->debtRepository->persist($debt);
    }

    public function getDebtById(int $id): ?Debt
    {
        return $this->debtRepository->find($id);
    }

    /**
     * @return Transaction[]
     */
    public function getAllDebtTransactionsForUser(User $user, array $filter): array
    {
        return $this->debtRepository->findTransactionsForUser($user, $filter);
    }

    /**
     * @return Debt[]
     */
    public function getAllDebtTransactionsForUserAndState(User $user, string $state): array
    {
        return $this->debtRepository->findAllDebtsForUserAndState($user, $state);
    }

    public function getCountForDebtTransactionsForUserAndState(User $user, string $state): int
    {
        return $this->debtRepository->getCountForAllDebtsForUserAndState($user, $state);
    }

    public function getTotalDebtsForUser(User $user): float
    {
        return $this->debtRepository->getTotalDebtsForUser($user);
    }
}
