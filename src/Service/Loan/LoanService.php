<?php

namespace App\Service\Loan;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Repository\LoanRepository;
use App\Service\Transaction\TransactionPartDataInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class LoanService
{
    public function __construct(
        private LoanFactory    $loanFactory,
        private LoanRepository $loanRepository
    )
    {
    }

    public function storeLoan(LoanCreateData $loanData): Loan
    {
        $loan = $this->loanFactory->createByData($loanData);

        $this->loanRepository->persist($loan);

        return $loan;
    }

    public function update(TransactionPartInterface $loan, TransactionPartDataInterface $data): void
    {
        $this->loanFactory->mapData($loan, $data);

        $this->loanRepository->persist($loan);
    }

    public function getLoanById(int $id): ?Loan
    {
        return $this->loanRepository->find($id);
    }

    /**
     * @return Transaction[]
     */
    public function getAllLoanTransactionsForUser(User $user, array $filter): array
    {
        return $this->loanRepository->findTransactionsForUser($user, $filter);
    }

    /**
     * @return Loan[]
     */
    public function getAllLoanTransactionsForUserAndSate(User $owner, string $state, float $amount): array
    {
        return $this->loanRepository->getAllLoanTransactionsForUserAndSate($owner, $state, $amount);
    }

    public function getCountForAllLoanTransactionsForUserAndSate(User $owner, string $state, float $amount): int
    {
        return $this->loanRepository->getCountForAllLoanTransactionsForUserAndSate($owner, $state, $amount);
    }

    /**
     * @return Loan[]
     */
    public function getAllExchangeLoansForDebt(Debt $debt): array
    {
        return $this->loanRepository->getAllExchangeLoansForDebt(
            $debt->getOwner(),
            Transaction::STATE_ACCEPTED,
            $debt->getAmount(),
            $debt->getTransaction()->getLoanerIds()
        );
    }

    public function getTotalLoansForUser(User $user): float
    {
        return $this->loanRepository->getTotalLoansForUser($user);
    }

    public function getLoanBySlug(string $getSlug)
    {
        return $this->loanRepository->findBy(['slug' => $getSlug]);
    }
}
