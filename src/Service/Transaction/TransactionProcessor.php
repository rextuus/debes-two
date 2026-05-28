<?php

namespace App\Service\Transaction;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Service\Debt\DebtService;
use App\Service\Debt\Form\DebtData;
use App\Service\Debt\Form\DebtUpdateData;
use App\Service\Loan\Form\LoanUpdateData;
use App\Service\Loan\LoanService;
use Doctrine\ORM\OptimisticLockException;
use Exception;

readonly class TransactionProcessor
{
    public function __construct(
        private DebtService $debtService,
        private LoanService $loanService,
        private TransactionService $transactionService
    ) {
    }

    public function accept(Debt $requestDebt): void
    {
        $transaction = $requestDebt->getTransaction();
        $loans = $transaction->getLoans();

        // Single Debt / Single Loan => |D|L|T| = |STATE_ACCEPTED|STATE_ACCEPTED|STATE_ACCEPTED|
        // update the debt
        if ($transaction->isSingleTransaction()) {
            $this->updateDebt($requestDebt, Transaction::STATE_ACCEPTED);

            // update the only loan
            $this->updateLoan($loans[0], Transaction::STATE_ACCEPTED);

            // set whole Transaction to acepted
            $this->updateTransaction($transaction, Transaction::STATE_ACCEPTED);
        }
        // Single Debt / Multiple Loan => |D|L|T| = |STATE_ACCEPTED|STATE_ACCEPTED|STATE_ACCEPTED|
        // update  current debt
        elseif (!$transaction->hasMultipleDebtors() && $transaction->hasMultipleLoaners()) {
            $this->updateDebt($requestDebt, Transaction::STATE_ACCEPTED);

            // update all loans
            foreach ($loans as $loan) {
                $this->updateLoan($loan, Transaction::STATE_ACCEPTED);
            }

            // set whole Transaction to accepted
            $this->updateTransaction($transaction, Transaction::STATE_ACCEPTED);
        }
        // Multiple Debt / Single Loan => |D|L|T| = |STATE_ACCEPTED|STATE_ACCEPTED?STATE_PARTIAL_ACCEPTED|STATE_ACCEPTED?STATE_PARTIAL_ACCEPTED|
        // update current debt
        elseif ($transaction->hasMultipleDebtors() && !$transaction->hasMultipleLoaners()) {
            $lastNonAcceptedOne = $transaction->isDebtTheLastNonAcceptedOne($requestDebt);
            $this->updateDebt($requestDebt, Transaction::STATE_ACCEPTED);
            // other debts dont change their state
            // if all debts are accepted => accept loan
            if ($lastNonAcceptedOne) {
                $this->updateLoan($loans[0], Transaction::STATE_ACCEPTED);
                // set transaction to accepted
                $this->updateTransaction($transaction, Transaction::STATE_ACCEPTED);
            } // else set loan partial accepted
            else {
                $this->updateLoan($loans[0], Transaction::STATE_PARTIAL_ACCEPTED);
                // set transaction to partial accepted
                $this->updateTransaction($transaction, Transaction::STATE_PARTIAL_ACCEPTED);
            }
        } // Multiple Debt / Multiple Loan => |D|L|T| = |STATE_ACCEPTED|STATE_ACCEPTED?STATE_PARTIAL_ACCEPTED|STATE_ACCEPTED?STATE_PARTIAL_ACCEPTED|
        elseif ($transaction->isMultipleTransaction()) {
            $isLastNonAccepted = $transaction->isDebtTheLastNonAcceptedOne($requestDebt);
            $this->updateDebt($requestDebt, Transaction::STATE_ACCEPTED);
            // other debts dont change their state

            // if all debts are accepted => accept loans
            if ($isLastNonAccepted) {
                foreach ($loans as $loan) {
                    $this->updateLoan($loan, Transaction::STATE_ACCEPTED);
                }
                // set transaction to accepted
                $this->updateTransaction($transaction, Transaction::STATE_ACCEPTED);
            } // else set loan partial accepted
            else {
                foreach ($loans as $loan) {
                    $this->updateLoan($loan, Transaction::STATE_PARTIAL_ACCEPTED);
                }
                // set transaction to partial accepted
                $this->updateTransaction($transaction, Transaction::STATE_PARTIAL_ACCEPTED);
            }
        } else {
            throw new Exception('Transaction has an non logical loan and debt distribution');
        }
    }

    protected function updateDebt(Debt $debt, string $state): void
    {
        $debtData = (new DebtUpdateData())->initFrom($debt);
        $debtData->setState($state);
        $this->debtService->update($debt, $debtData);
    }

    protected function updateLoan($singleLoan, string $state): void
    {
        $loanData = (new LoanUpdateData())->initFrom($singleLoan);
        $loanData->setState($state);
        $this->loanService->update($singleLoan, $loanData);
    }

    protected function updateLoanInState(Loan $singleLoan, string $state, string $mandatoryState): void
    {
        $loanData = new LoanUpdateData()->initFrom($singleLoan);
        $loanData->setState($state);
        if ($singleLoan->getState() === $mandatoryState) {
            $this->loanService->update($singleLoan, $loanData);
        }
    }

    protected function updateTransaction(Transaction $transaction, string $state): void
    {
        $transactionData = new TransactionUpdateData();
        $transactionData->initFrom($transaction);
        $transactionData->setState($state);
        $this->transactionService->update($transaction, $transactionData);
    }

    public function process(Debt $requestDebt)
    {
        $transaction = $requestDebt->getTransaction();
        $loans = $transaction->getLoans();

        // Single Debt / Single Loan => |D|L|T| = |Transaction::STATE_CLEARED|Transaction::STATE_CLEARED|Transaction::STATE_CLEARED|
        // update the debt
        if ($transaction->isSingleTransaction()) {
            $this->updateDebt($requestDebt, Transaction::STATE_CLEARED);

            // update the only loan
            $this->updateLoan($loans[0], Transaction::STATE_CLEARED);

            // set whole Transaction to acepted
            $this->updateTransaction($transaction, Transaction::STATE_CLEARED);
        }


        // Single Debt / Multiple Loan => |D|L|T| = |Transaction::STATE_CLEARED|Transaction::STATE_CLEARED|Transaction::STATE_CLEARED|
        if ($transaction->hasMultipleSide() && $transaction->hasMultipleLoaners() && !$transaction->hasMultipleDebtors(
            )) {
            // update  current debt
            $this->updateDebt($requestDebt, Transaction::STATE_CLEARED);

            // update all loans
            foreach ($loans as $loan) {
                $this->updateLoan($loan, Transaction::STATE_CLEARED);
            }

            // set whole Transaction to accepted
            $this->updateTransaction($transaction, Transaction::STATE_CLEARED);
        }


        // Multiple Debt / Single Loan => |D|L|T| = |Transaction::STATE_CLEARED|Transaction::STATE_CLEARED?Transaction::STATE_PARTIAL_CLEARED|Transaction::STATE_CLEARED?Transaction::STATE_PARTIAL_CLEARED|
        if ($transaction->hasMultipleSide() && !$transaction->hasMultipleLoaners() && $transaction->hasMultipleDebtors(
            )) {
            $isLastOne = $transaction->isDebtTheLastNonClearedOne($requestDebt);

            // update current debt
            $this->updateDebt($requestDebt, Transaction::STATE_CLEARED);
            // TODO other debts dont change their state, but maybe we have to change them
            // if all debts are accepted => accept loan
            if ($isLastOne) {
                $this->updateLoan($loans[0], Transaction::STATE_CLEARED);
                // set transaction to accepted
                $this->updateTransaction($transaction, Transaction::STATE_CLEARED);
            } // else set loan partial accepted
            else {
                $this->updateLoan($loans[0], Transaction::STATE_PARTIAL_CLEARED);
                // set transaction to partial accepted
                $this->updateTransaction($transaction, Transaction::STATE_PARTIAL_CLEARED);
            }
        }

        if ($transaction->isMultipleTransaction()) {
            // Multiple Debt / Single Loan => |D|L|T| = |Transaction::STATE_CLEARED|Transaction::STATE_CLEARED?Transaction::STATE_PARTIAL_CLEARED|Transaction::STATE_CLEARED?Transaction::STATE_PARTIAL_CLEARED|
            $this->updateDebt($requestDebt, Transaction::STATE_CLEARED);
            // TODO other debts dont change their state, but maybe we have to change them

            // if all debts are accepted => accept loans
            if ($transaction->isDebtTheLastNonClearedOne($requestDebt)) {
                foreach ($loans as $loan) {
                    $this->updateLoan($loan, Transaction::STATE_CLEARED);
                }
                // set transaction to accepted
                $this->updateTransaction($transaction, Transaction::STATE_CLEARED);
            } // else set loan partial accepted
            else {
                foreach ($loans as $loan) {
                    $this->updateLoanInState(
                        $loan,
                        Transaction::STATE_PARTIAL_CLEARED,
                        Transaction::STATE_PARTIAL_ACCEPTED
                    );
                }
                // set transaction to partial accepted
                $this->updateTransaction($transaction, Transaction::STATE_PARTIAL_CLEARED);
            }
        }
    }
}