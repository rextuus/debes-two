<?php

namespace App\Service\Transaction;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionStateChangeEvent;
use App\Entity\User;
use App\Exception\UserNotCorrectParticipantOfTransaction;
use App\Repository\TransactionRepository;
use App\Service\Debt\DebtCreateData;
use App\Service\Debt\DebtService;
use App\Service\Debt\DebtUpdateData;
use App\Service\Loan\LoanCreateData;
use App\Service\Loan\LoanDto;
use App\Service\Loan\LoanService;
use App\Service\Loan\LoanUpdateData;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventData;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use DateTime;
use Doctrine\DBAL\Exception;

class TransactionService
{
    const DEBTOR_VIEW = 'debtor';
    const LOANER_VIEW = 'loaner';

    public function __construct(
        private TransactionFactory            $transactionFactory,
        private TransactionRepository         $transactionRepository,
        private DebtService                   $debtService,
        private LoanService                   $loanService,
        private DtoProvider                   $dtoProvider,
        private TransactionChangeEventService $transactionChangeEventService
    )
    {
    }

    public function storeTransaction(TransactionData $transactionData, bool $persist = true): Transaction
    {
        $transaction = $this->transactionFactory->createByData($transactionData);

        if ($persist) {
            $this->transactionRepository->persist($transaction);
        }

        return $transaction;
    }

    public function update(Transaction $transaction, TransactionUpdateData $data = null): Transaction
    {
        // TODO Add variant to change event paypal/bankaccount/exchange
        // TODO after transaction is set to cleared it will be set to partial cleared why?
        $eventChangeData = null;
        if (!is_null($data)) {
            $oldState = $transaction->getState();
            $newState = $data->getState();

            if ($oldState !== $newState) {
                $eventChangeData = new TransactionChangeEventData();
                $eventChangeData->setOldState($oldState);
                $eventChangeData->setNewState($newState);
                $eventChangeData->setCreated(new DateTime());
                $eventChangeData->setTransaction($transaction);
                $eventChangeData->setType(TransactionStateChangeEvent::TYPE_BLANK);
                if ($newState === Transaction::STATE_PARTIAL_CLEARED || $newState === Transaction::STATE_CLEARED) {
                    $type = $data->getChangeType();
                    $eventChangeData->setType($type);

                    $eventChangeData->setTarget($data->getTarget());
                }
            }

            $this->transactionFactory->mapData($transaction, $data);
        }
        $this->transactionRepository->persist($transaction);

        if ($eventChangeData) {
            $this->transactionChangeEventService->storeTransactionChangeEvent($eventChangeData);
        }

        return $transaction;
    }

    public function storeSingleTransaction(TransactionData $data, User $requester): Transaction
    {
        $data->setState(Transaction::STATE_READY);
        $transaction = $this->storeTransaction($data);

        $debtData = (new DebtCreateData())->initFromData($data);
        $debtData->setTransaction($transaction);
        $debt = $this->debtService->storeDebt($debtData);

        $loanData = (new LoanCreateData())->initFromData($data, $requester);
        $loanData->setTransaction($transaction);
        $loan = $this->loanService->storeLoan($loanData);

        $transaction->addDebt($debt);
        $transaction->addLoan($loan);
        $this->update($transaction);
        return $transaction;
    }

    public function storeMultipleTransaction(TransactionCreateMultipleData $data): Transaction
    {
        $transactionData = new TransactionCreateData();
        $transactionData->setReason($data->getReason());
        $transactionData->setAmount($data->getCompleteAmount());
        $transactionData->setState(Transaction::STATE_READY);

        $transaction = $this->storeTransaction($transactionData);

        foreach ($data->getDebtorsData() as $debtData) {
            $debtData->setTransaction($transaction);
            $debtData->setPaid(false);
            $debtData->setCreated(new DateTime());
            $debtData->setReason($data->getReason());
            $debtData->setState(Transaction::STATE_READY);
            $debt = $this->debtService->storeDebt($debtData);
            $transaction->addDebt($debt);
        }

        foreach ($data->getLoanersData() as $loanData) {
            $loanData->setTransaction($transaction);
            $loanData->setPaid(false);
            $loanData->setCreated(new DateTime());
            $loanData->setReason($data->getReason());
            $loanData->setState(Transaction::STATE_READY);
            $loan = $this->loanService->storeLoan($loanData);
            $transaction->addLoan($loan);
        }

        $transactionUpdateData = (new TransactionUpdateData())->initFrom($transaction);
        $this->update($transaction, $transactionUpdateData);

        return $transaction;
    }

    /**
     * @return TransactionDtos\TransactionDto[]
     */
    public function getAllTransactionBelongingUser(User $owner): array
    {
        $dtos = array();
        $debtTransactions = $this->debtService->getAllDebtTransactionsForUser($owner);
        foreach ($debtTransactions as $transaction) {
            $dtos[] = $this->dtoProvider->createTransactionDto($transaction, true);
        }
        $loanTransactions = $this->loanService->getAllLoanTransactionsForUser($owner);
        foreach ($loanTransactions as $transaction) {
            $dtos[] = $this->dtoProvider->createTransactionDto($transaction, false);
        }
        return $dtos;
    }

    public function getTotalDebtsForUser(User $owner): float
    {
        return $this->debtService->getTotalDebtsForUser($owner);
    }

    public function getTotalLoansForUser(User $owner): float
    {
        return $this->loanService->getTotalLoansForUser($owner);
    }

    /**
     * @return TransactionDtos\TransactionDto[]
     */
    public function getAllDebtTransactionsForUserAndState(User $owner, string $state): array
    {
        $dtos = [];
        $debts = $this->debtService->getAllDebtTransactionsForUserAndState($owner, $state);
        foreach ($debts as $debt) {
            $dtos[] = $this->dtoProvider->createTransactionDto($debt->getTransaction(), true);
        }
        return $dtos;
    }

    public function getCountForDebtTransactionsForUserAndState(User $user, string $state): int
    {
        return $this->debtService->getCountForDebtTransactionsForUserAndState($user, $state);
    }

    public function createDtoFromTransaction(
        Transaction $transaction,
        bool        $isDebtVariant
    ): TransactionDtos\TransactionDto
    {
        return $this->dtoProvider->createTransactionDto($transaction, $isDebtVariant);
    }

    /**
     * @return LoanDto[]
     */
    public function getAllLoanTransactionPartsForUserAndStateDtoVariant(User $owner, string $state): array
    {
        $dtos = array();
        $loans = $this->loanService->getAllLoanTransactionsForUserAndSate($owner, $state, 0.0);
        foreach ($loans as $loan) {
            $dtos[] = $this->dtoProvider->createLoanDto($loan);
        }
        return $dtos;
    }

    /**
     * @return Loan[]
     */
    public function getAllLoanTransactionsForUserAndState(User $owner, string $state): array
    {
        return $this->loanService->getAllLoanTransactionsForUserAndSate($owner, $state, 0.0);
    }

    /**
     * @return TransactionDtos\TransactionDto[]
     */
    public function getAllLoanTransactionsForUserAndState2(User $owner, string $state): array
    {
        $dtos = [];
        $loans = $this->loanService->getAllLoanTransactionsForUserAndSate($owner, $state, 0.0);
        foreach ($loans as $loan) {
            $dtos[] = $this->dtoProvider->createTransactionDto($loan->getTransaction(), false);
        }
        return $dtos;
    }

    public function getCountForAllLoanTransactionsForUserAndSate(User $owner, string $state): int
    {
        return $this->loanService->getCountForAllLoanTransactionsForUserAndSate($owner, $state, 0.0);
    }

    public function confirmTransaction(Transaction $transaction): void
    {
        $transactionData = (new TransactionUpdateData())->initFrom($transaction);
        $transactionData->setState(Transaction::STATE_CONFIRMED);
        $this->updateInclusive($transaction, $transactionData);
    }

    public function declineDebt(Debt $debt): void
    {
        $debtData = (new DebtUpdateData())->initFrom($debt);
        $debtData->setState(Transaction::STATE_DECLINED);
        $this->debtService->update($debt, $debtData);
    }

    /**
     * @throws UserNotCorrectParticipantOfTransaction
     * @throws Exception
     */
    public function checkRequestForVariant(
        User        $requester,
        Transaction $transaction,
        string      $variant,
        string      $state
    ): bool
    {
        if ($variant === self::DEBTOR_VIEW) {
            $debt = $this->getDebtPartOfUserForTransaction($transaction, $requester);
            if (is_null($debt)) {
                throw new UserNotCorrectParticipantOfTransaction('User is not a loaner of this transaction');
            }
            if ($debt->getState() !== $state) {
                throw new Exception('TransactionPart is not in correct sate');
            }
            return true;
        } elseif ($variant === self::LOANER_VIEW) {
            $loan = $this->getLoanPartOfUserForTransaction($transaction, $requester);
            if (is_null($loan)) {
                throw new UserNotCorrectParticipantOfTransaction('User is not a loaner of this transaction');
            }
            if ($loan->getState() !== $state) {
                throw new Exception('TransactionPart is not in correct sate');
            }
            return true;
        } else {
            throw new Exception('User is not involved in this transaction');
        }
    }

    public function getTransactionBySlug(string $slug): ?Transaction
    {
        return $this->transactionRepository->findOneBy(['slug' => $slug]);
    }

    public function updateInclusive(?Transaction $transaction, TransactionUpdateData $transactionUpdateData)
    {
        $this->update($transaction, $transactionUpdateData);

        $loan = $transaction->getLoans()[0];
        $loanData = (new LoanUpdateData())->initFrom($loan);
        $loanData->setAmount($transaction->getAmount());
        $loanData->setReason($transaction->getReason());
        $loanData->setState($transaction->getState());
        $this->loanService->update($loan, $loanData);

        $debt = $transaction->getDebts()[0];
        $debtData = (new DebtUpdateData())->initFrom($debt);
        $debtData->setAmount($transaction->getAmount());
        $debtData->setReason($transaction->getReason());
        $debtData->setState($transaction->getState());
        $this->debtService->update($debt, $debtData);
    }

    public function updateInclusiveMulti(Transaction $transaction, TransactionUpdateData $transactionUpdateData)
    {
        $transaction = $this->update($transaction, $transactionUpdateData);
        foreach ($transaction->getLoans() as $loan) {
            $loanData = (new LoanUpdateData())->initFrom($loan);
            $loanData->setAmount($transaction->getAmount());
            $loanData->setReason($transaction->getReason());
            $loanData->setState($transactionUpdateData->getState());
            $this->loanService->update($loan, $loanData);
        }
        foreach ($transaction->getDebts() as $debt) {
            $debtData = (new DebtUpdateData())->initFrom($debt);
            $debtData->setAmount($transaction->getAmount());
            $debtData->setReason($transaction->getReason());
            $debtData->setState($transactionUpdateData->getState());
            $this->debtService->update($debt, $debtData);
        }
    }

    public function getDebtPartOfUserForTransaction(Transaction $transaction, User $user): ?Debt
    {
        foreach ($transaction->getDebts() as $debt) {
            if ($debt->getOwner() === $user) {
                return $debt;
            }
        }
        return null;
    }

    public function getLoanPartOfUserForTransaction(Transaction $transaction, User $user): ?Loan
    {
        foreach ($transaction->getLoans() as $loan) {
            if ($loan->getOwner() === $user) {
                return $loan;
            }
        }
        return null;
    }

    public function getTransactionById(int $int)
    {
        return $this->transactionRepository->find($int);
    }

    /**
     * @return Transaction[]
     */
    public function getAll(): array
    {
        return $this->transactionRepository->findAll();
    }

    public function getTransactionCountBetweenUsers(User $debtor, User $loaner): int
    {
        return $this->transactionRepository->getTransactionCountBetweenUsers($debtor, $loaner);
    }

    public function getTotalDebtsBetweenUsers(User $debtor, User $loaner): int
    {
        $amount = $this->transactionRepository->getTotalDebtsBetweenUsers($debtor, $loaner);
        if (!$amount){
            return 0;
        }
        return $amount;
    }
}
