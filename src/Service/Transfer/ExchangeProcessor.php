<?php

namespace App\Service\Transfer;

use App\Entity\Debt;
use App\Entity\Exchange;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\TransactionStateChangeEvent;
use App\Entity\User;
use App\EntityListener\Event\TransactionExchangeEvent;
use App\Service\Debt\DebtService;
use App\Service\Debt\Form\DebtUpdateData;
use App\Service\Exchange\ExchangeService;
use App\Service\Exchange\Form\ExchangeCreateData;
use App\Service\Loan\Form\LoanUpdateData;
use App\Service\Loan\LoanService;
use App\Service\Transaction\DtoProvider;
use App\Service\Transaction\Transaction\Form\TransactionPartDataInterface;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionUpdateData;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * ExchangeProcessor
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 *
 */
class ExchangeProcessor
{
    public function __construct(
        private TransactionService $transactionService,
        private ExchangeService $exchangeService,
        private LoanService $loanService,
        private DebtService $debtService,
        private DtoProvider $dtoProvider,
        private EventDispatcherInterface $dispatcher

    ) {
    }


    /**
     * findExchangeCandidatesForTransaction
     *
     * @param Debt $debt
     *
     * @return ExchangeCandidateSet
     */
    public function findExchangeCandidatesForTransactionPart(Debt $debt): ExchangeCandidateSet
    {
        $loanersOfGivenDebt = [];
        $loans = $debt->getTransaction()->getLoans();
        foreach ($loans as $loan) {
            $loanersOfGivenDebt[$loan->getOwner()->getId()] = $loan->getOwner()->getId();
        }

        // debt comes in => we search for all Loans where debt.owner is owner
        $candidates = $this->transactionService->getAllLoanTransactionsForUserAndState(
            $debt->getOwner(),
            Transaction::STATE_ACCEPTED
        );

        // check if candidate loans transaction has a debtor t
        $fittingCandidates = [];
        foreach ($candidates as $candidate) {
            $candidateTransaction = $candidate->getTransaction();
            $candidateDebts = $candidateTransaction->getDebts();
            $debtorsOfCandidate = [];
            foreach ($candidateDebts as $candidateDebt) {
                $debtorsOfCandidate[$candidateDebt->getOwner()->getId()] = $candidateDebt->getOwner()->getId();
            }
            $candidateIsGood = false;
            foreach ($loanersOfGivenDebt as $loanersOfGivenDeb) {
                if (array_key_exists($loanersOfGivenDeb, $debtorsOfCandidate)) {
                    $candidateIsGood = true;
                }
            }

            if ($candidateIsGood) {
                $fittingCandidates[] = $candidate;
            }
        }

        $fittingDtos = [];
        foreach ($fittingCandidates as $fittingCandidate) {
            $fittingDtos[] = $this->dtoProvider->createLoanDto($fittingCandidate);
        }

        $exchangeCandidateSet = new ExchangeCandidateSet();
        $exchangeCandidateSet->setFittingCandidates($fittingCandidates);
        $exchangeCandidateSet->setFittingCandidatesDtoVersion($fittingDtos);
        $exchangeCandidateSet->setNonFittingCandidates([]);
        $exchangeCandidateSet->setNonFittingCandidatesDtoVersion([]);
        return $exchangeCandidateSet;

        $fittingCandidates = [];
        $nonFittingCandidates = [];
//        foreach ($candidates as $candidate) {
//            /** @var Transaction $candidate */
//            if ($candidate->getAmount() >= $debt->getAmount()) {
//                $fittingCandidates[] = $candidate;
//            } else {
//                $nonFittingCandidates[] = $candidate;
//            }
//        }

        $exchangeCandidateSet = new ExchangeCandidateSet();
        $exchangeCandidateSet->setFittingCandidatesDtoVersion($candidates);
        $exchangeCandidateSet->setNonFittingCandidatesDtoVersion($nonFittingCandidates);

        // get loan variant
        $candidates = $this->transactionService->getAllLoanTransactionsForUserAndState(
            $debt->getOwner(),
            Transaction::STATE_ACCEPTED
        );
        $exchangeCandidateSet->setFittingCandidates($candidates);
        $exchangeCandidateSet->setNonFittingCandidates([]);

        return $exchangeCandidateSet;
    }

    public function calculateExchange(Transaction $debtTransaction, Transaction $loanTransaction): ExchangeDto
    {
        $exchangeDto = (new ExchangeDto())->initFromTransactions($debtTransaction, $loanTransaction);
        $difference = $loanTransaction->getAmount() - $debtTransaction->getAmount();
        $exchangeDto->setDifference($difference);
        return $exchangeDto;
    }

    public function exchangeDebtAndLoan(Debt $debt, Loan $loan): void
    {
        // update debt and transaction

        // debt is greater than loan => set loan to 0 and debt to difference
        if ($debt->getAmount() > $loan->getAmount()) {
            $difference = $debt->getAmount() - $loan->getAmount();
            $transactionDifference = $difference;
            $debtUpdateData = new DebtUpdateData();
            $debtUpdateData->setAmount($difference);
            $debtUpdateData->setState(Transaction::STATE_PARTIAL_CLEARED);

            $loanUpdateData = new LoanUpdateData();
            $loanUpdateData->setAmount(0.0);
            $loanUpdateData->setPaid(true);
            $loanUpdateData->setState(Transaction::STATE_CLEARED);
        } else {
            $difference = $loan->getAmount() - $debt->getAmount();
            $transactionDifference = $difference;
            $debtUpdateData = new DebtUpdateData();
            $debtUpdateData->setAmount(0.0);
            $debtUpdateData->setState(Transaction::STATE_CLEARED);
            $debtUpdateData->setPaid(true);

            $loanUpdateData = new LoanUpdateData();
            $loanUpdateData->setAmount($difference);
            $loanUpdateData->setState(Transaction::STATE_PARTIAL_CLEARED);
        }
        $debtUpdateData->setEdited(new DateTime());
        $loanUpdateData->setEdited(new DateTime());
        // create exchange
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function exchangeTransactionParts(
        TransactionPartInterface $transactionPart1,
        TransactionPartInterface $transactionPart2
    ): void {
        if ($transactionPart1->getAmount() >= $transactionPart2->getAmount()) {
            $exchanges = $this->fillExchangeCreateDataSets($transactionPart1, $transactionPart2);
            $this->fillTransactionUpdateDataSets($transactionPart1, $transactionPart2, $exchanges);
        } else {
            $exchanges = $this->fillExchangeCreateDataSets($transactionPart2, $transactionPart1);
            $this->fillTransactionUpdateDataSets($transactionPart2, $transactionPart1, $exchanges);
        }

        // fire exchangeEvents
        foreach ($exchanges as $exchange) {
            $event = new TransactionExchangeEvent($exchange);
            $this->dispatcher->dispatch($event, TransactionExchangeEvent::NAME);
        }
    }

    /**
     * @return Exchange[]
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function fillExchangeCreateDataSets(
        TransactionPartInterface $transactionWithHigherAmount,
        TransactionPartInterface $transactionWithLowerAmount
    ): array {
        $exchangeCreationDataHigher = new ExchangeCreateData();
        $exchangeCreationDataHigher->setTransaction($transactionWithHigherAmount->getTransaction());
        $exchangeCreationDataLower = new ExchangeCreateData();
        $exchangeCreationDataLower->setTransaction($transactionWithLowerAmount->getTransaction());

        $exchangeCreationDataHigher->setAmount($transactionWithLowerAmount->getAmount());
        $exchangeCreationDataLower->setAmount($transactionWithLowerAmount->getAmount());

        $exchangeCreationDataHigher->setRemainingAmount(
            $transactionWithHigherAmount->getAmount() - $transactionWithLowerAmount->getAmount()
        );
        $exchangeCreationDataLower->setRemainingAmount(0);

        if ($transactionWithHigherAmount->isDebt()) {
            $exchangeCreationDataHigher->setDebt($transactionWithHigherAmount);
            $exchangeCreationDataHigher->setLoan($transactionWithLowerAmount);
            $exchangeCreationDataLower->setDebt($transactionWithHigherAmount);
            $exchangeCreationDataLower->setLoan($transactionWithLowerAmount);
        } else {
            $exchangeCreationDataHigher->setDebt($transactionWithLowerAmount);
            $exchangeCreationDataHigher->setLoan($transactionWithHigherAmount);
            $exchangeCreationDataLower->setDebt($transactionWithLowerAmount);
            $exchangeCreationDataLower->setLoan($transactionWithHigherAmount);
        }

        $exchanges['higher'] = $this->exchangeService->storeExchange($exchangeCreationDataHigher);
        $exchanges['lower'] = $this->exchangeService->storeExchange($exchangeCreationDataLower);

        return $exchanges;
    }
    //TODO: Asicht für Multitransaktionen fixen

    /**
     * @param TransactionPartInterface $transactionPartWithHigherAmount
     * @param TransactionPartInterface $transactionPartWithLowerAmount
     * @param Exchange[] $exchanges
     */
    private function fillTransactionUpdateDataSets(
        TransactionPartInterface $transactionPartWithHigherAmount,
        TransactionPartInterface $transactionPartWithLowerAmount,
        array $exchanges
    ): void {
        // we got 4 scenarios:
        // 1. high is single | low is single
        // 2. high is multi  | low is single
        // 3. high is single | low is multi
        // 4. high is multi  | low is multi

        // we need four transactions parts in the end:
        $user1 = null;
        $user2 = null;

        // HIGH = single | LOW = single
        $higherAmountTransaction = $transactionPartWithHigherAmount->getTransaction();
        $lowerAmountTransaction = $transactionPartWithLowerAmount->getTransaction();
        if ($higherAmountTransaction->isSingleTransaction() && $lowerAmountTransaction->isSingleTransaction()) {
//            $this->updateSingleTransactions($transactionPartWithHigherAmount->getTransaction(), $transactionPartWithLowerAmount->getTransaction());
            $user1 = $higherAmountTransaction->getDebtor();
            $user2 = $higherAmountTransaction->getLoaner();

            $updateDataCollection = $this->prepareUpdateDataSets(
                $user1,
                $user2,
                $higherAmountTransaction,
                $lowerAmountTransaction,
                $exchanges
            );
            $highTransactionData = $updateDataCollection->getTransactionHighData();
            $highTransactionData->setState(Transaction::STATE_ACCEPTED);
            $this->transactionService->updateInclusive($higherAmountTransaction, $highTransactionData);

            $lowTransactionData = $updateDataCollection->getTransactionLowData();
            $lowTransactionData->setState(Transaction::STATE_CLEARED);
            $this->transactionService->updateInclusive($lowerAmountTransaction, $lowTransactionData);
        }
        // HIGH = single | LOW = multi
        if ($transactionPartWithHigherAmount->getTransaction()->isSingleTransaction(
            ) && $transactionPartWithLowerAmount->getTransaction()->hasMultipleSide()) {
            dump('HIGH = single | LOW = multi');
//            $this->updateHighSingleAndLowMultipleTransaction($transactionPartWithHigherAmount, $transactionPartWithLowerAmount);
            $user1 = $higherAmountTransaction->getDebtor();
            $user2 = $higherAmountTransaction->getLoaner();

            $updateDataCollection = $this->prepareUpdateDataSets(
                $user1,
                $user2,
                $higherAmountTransaction,
                $lowerAmountTransaction,
                $exchanges
            );
            $updateDataCollection->setStateTransactionHigh(Transaction::STATE_ACCEPTED);
            if ($updateDataCollection->getTransactionHighData()->getAmount() == 0.0) {
                $updateDataCollection->setStateTransactionHigh(Transaction::STATE_CLEARED);
            }

            $updateDataCollection->setStateTransactionLow(Transaction::STATE_PARTIAL_CLEARED);
            if ($updateDataCollection->getTransactionLowData()->getAmount() == 0.0) {
                $updateDataCollection->setStateTransactionLow(Transaction::STATE_CLEARED);
            }

            $this->transactionService->update(
                $updateDataCollection->getTransactionHigh(),
                $updateDataCollection->getTransactionHighData()
            );
            $this->transactionService->update(
                $updateDataCollection->getTransactionLow(),
                $updateDataCollection->getTransactionLowData()
            );
        }
        // HIGH = multi | LOW = single
        if ($transactionPartWithHigherAmount->getTransaction()->hasMultipleSide(
            ) && $transactionPartWithLowerAmount->getTransaction()->isSingleTransaction()) {
            $user1 = $lowerAmountTransaction->getDebtor();
            $user2 = $lowerAmountTransaction->getLoaner();

            // do in everyCase
            $updateDataCollection = $this->prepareUpdateDataSets(
                $user1,
                $user2,
                $higherAmountTransaction,
                $lowerAmountTransaction,
                $exchanges
            );
            $updateDataCollection->setStateTransactionHigh(Transaction::STATE_PARTIAL_CLEARED);
            if ($updateDataCollection->getTransactionHighData()->getAmount() == 0.0) {
                $updateDataCollection->setStateTransactionHigh(Transaction::STATE_CLEARED);
            }

            $updateDataCollection->setStateTransactionLow(Transaction::STATE_ACCEPTED);
            if ($updateDataCollection->getTransactionLowData()->getAmount() == 0.0) {
                $updateDataCollection->setStateTransactionLow(Transaction::STATE_CLEARED);
            }

            $this->transactionService->update(
                $updateDataCollection->getTransactionHigh(),
                $updateDataCollection->getTransactionHighData()
            );
            $this->transactionService->update(
                $updateDataCollection->getTransactionLow(),
                $updateDataCollection->getTransactionLowData()
            );
        }
        // HIGH = multi | LOW = multi
        if ($transactionPartWithHigherAmount->getTransaction()->hasMultipleSide(
            ) && $transactionPartWithLowerAmount->getTransaction()->hasMultipleSide()) {
            dump('HIGH = multi | LOW = multi');
            // askingUser is owner of both parts => one is his loan and other is his debt
            $askingUser = $transactionPartWithHigherAmount->getOwner();
            // next we have to find an exchange partner => this is a user that has a part in both corresponding debts or loans array
            $correspondingTransactionPartsHigh = $higherAmountTransaction->getDebts();
            if ($transactionPartWithHigherAmount->isDebt()) {
                $correspondingTransactionPartsHigh = $higherAmountTransaction->getLoans();
            }
            $correspondingTransactionPartsLow = $lowerAmountTransaction->getDebts();
            if ($transactionPartWithLowerAmount->isDebt()) {
                $correspondingTransactionPartsLow = $lowerAmountTransaction->getLoans();
            }

            // find parts of users, that exist in both arrays
            $exchangeCandidates = [];
            foreach ($correspondingTransactionPartsHigh->toArray() as $transactionPart) {
                /** @var TransactionPartInterface $transactionPart */
                foreach ($correspondingTransactionPartsLow->toArray() as $candidate) {
                    /** @var TransactionPartInterface $candidate */
                    if ($transactionPart->getOwner() === $candidate->getOwner() && $transactionPart->getOwner(
                        ) !== $askingUser) {
                        $exchangeCandidates[] = $transactionPart->getOwner();
                    }
                }
            }

            // TODO we use the first exchange candidate to keep it simple. But it would be also an option
            // to search for the highest one or give the requester the option to choose
            // do in everyCase
            $updateDataCollection = $this->prepareUpdateDataSets(
                $askingUser,
                $exchangeCandidates[0],
                $higherAmountTransaction,
                $lowerAmountTransaction,
                $exchanges
            );
            $updateDataCollection->setStateTransactionHigh(Transaction::STATE_PARTIAL_CLEARED);
            if ($updateDataCollection->getTransactionHighData()->getAmount() == 0.0) {
                $updateDataCollection->setStateTransactionHigh(Transaction::STATE_CLEARED);
            }

            $updateDataCollection->setStateTransactionLow(Transaction::STATE_PARTIAL_CLEARED);
            if ($updateDataCollection->getTransactionLowData()->getAmount() == 0.0) {
                $updateDataCollection->setStateTransactionLow(Transaction::STATE_CLEARED);
            }

            $this->transactionService->update(
                $updateDataCollection->getTransactionHigh(),
                $updateDataCollection->getTransactionHighData()
            );
            $this->transactionService->update(
                $updateDataCollection->getTransactionLow(),
                $updateDataCollection->getTransactionLowData()
            );
        }
    }

    private function updateTransactionPart(
        TransactionPartInterface $transactionPart,
        TransactionPartDataInterface $transactionPartData
    ): void {
        if ($transactionPart->isDebt()) {
            $this->debtService->update($transactionPart, $transactionPartData);
        }
        $this->loanService->update($transactionPart, $transactionPartData);
    }

    private function getTransactionPartUpdateData(TransactionPartInterface $transactionPart
    ): TransactionPartDataInterface {
        if ($transactionPart->isDebt()) {
            return (new DebtUpdateData())->initFrom($transactionPart);
        }
        return (new LoanUpdateData())->initFrom($transactionPart);
    }

    /**
     * @param User $user1
     * @param User $user2
     * @param Transaction $higherAmountTransaction
     * @param Transaction $lowerAmountTransaction
     * @return TransactionPartInterface[]
     */
    private function findEffectedTransactionParts(
        User $user1,
        User $user2,
        Transaction $higherAmountTransaction,
        Transaction $lowerAmountTransaction
    ): array {
        $transactionParts = [];
        $counter = 0;
        foreach ($higherAmountTransaction->getLoans() as $loan) {
            if ($loan->getOwner() == $user1 || $loan->getOwner() == $user2) {
                $transactionParts[$counter] = $loan;
                $counter++;
            }
        }
        foreach ($higherAmountTransaction->getDebts() as $debt) {
            if ($debt->getOwner() == $user1 || $debt->getOwner() == $user2) {
                $transactionParts[$counter] = $debt;
                $counter++;
            }
        }

        foreach ($lowerAmountTransaction->getLoans() as $loan) {
            if ($loan->getOwner() == $user1 || $loan->getOwner() == $user2) {
                $transactionParts[$counter] = $loan;
                $counter++;
            }
        }
        foreach ($lowerAmountTransaction->getDebts() as $debt) {
            if ($debt->getOwner() == $user1 || $debt->getOwner() == $user2) {
                $transactionParts[$counter] = $debt;
                $counter++;
            }
        }
        return $transactionParts;
    }

    /**
     * @param User $user1
     * @param User $user2
     * @param Transaction $higherAmountTransaction
     * @param Transaction $lowerAmountTransaction
     * @param array $exchanges
     * @return TransactionUpdateDataCollection
     */
    private function prepareUpdateDataSets(
        User $user1,
        User $user2,
        Transaction $higherAmountTransaction,
        Transaction $lowerAmountTransaction,
        array $exchanges
    ): TransactionUpdateDataCollection {
        $collection = new TransactionUpdateDataCollection();

        $activeTransactionParts = $this->findEffectedTransactionParts(
            $user1,
            $user2,
            $higherAmountTransaction,
            $lowerAmountTransaction
        );
        $lowestAmount = PHP_INT_MAX;
        foreach ($activeTransactionParts as $part) {
            if ($part->getAmount() < $lowestAmount) {
                $lowestAmount = $part->getAmount();
            }
        }

        // transaction Updates
        $transactionUpdateDataHigh = (new TransactionUpdateData())->initFrom($higherAmountTransaction);
        $transactionUpdateDataHigh->setChangeType(TransactionStateChangeEvent::TYPE_EXCHANGE_ACCOUNT);
        $transactionUpdateDataHigh->setAmount($transactionUpdateDataHigh->getAmount() - $lowestAmount);
        $transactionUpdateDataHigh->setTarget($exchanges['higher']);
        $collection->setTransactionHighData($transactionUpdateDataHigh);
        $collection->setTransactionHigh($higherAmountTransaction);
        $transactionUpdateDataLow = (new TransactionUpdateData())->initFrom($lowerAmountTransaction);
        $transactionUpdateDataLow->setChangeType(TransactionStateChangeEvent::TYPE_EXCHANGE_ACCOUNT);
        $transactionUpdateDataLow->setAmount($transactionUpdateDataLow->getAmount() - $lowestAmount);
        $transactionUpdateDataLow->setTarget($exchanges['lower']);
        $collection->setTransactionLowData($transactionUpdateDataLow);
        $collection->setTransactionLow($lowerAmountTransaction);

        // transactionPart Updates
        foreach ($activeTransactionParts as $part) {
            $transactionPartUpdateDataSet = $this->getTransactionPartUpdateData($part);
            $transactionPartUpdateDataSet->setAmount($transactionPartUpdateDataSet->getAmount() - $lowestAmount);
            $transactionPartUpdateDataSet->setState(Transaction::STATE_ACCEPTED);

            if ($transactionPartUpdateDataSet->getAmount() == 0.0) {
                $transactionPartUpdateDataSet->setState(Transaction::STATE_CLEARED);
            }

            // set to collection
            if ($part->getTransaction() === $higherAmountTransaction && $part->isLoan()) {
                $collection->setTransactionPartHighLoanData($transactionPartUpdateDataSet);
                $collection->setTransactionPartHighLoan($part);
            }
            if ($part->getTransaction() === $higherAmountTransaction && $part->isDebt()) {
                $collection->setTransactionPartHighDebtData($transactionPartUpdateDataSet);
                $collection->setTransactionPartHighDebt($part);
            }
            if ($part->getTransaction() === $lowerAmountTransaction && $part->isLoan()) {
                $collection->setTransactionPartLowLoanData($transactionPartUpdateDataSet);
                $collection->setTransactionPartLowLoan($part);
            }
            if ($part->getTransaction() === $lowerAmountTransaction && $part->isDebt()) {
                $collection->setTransactionPartLowDebtData($transactionPartUpdateDataSet);
                $collection->setTransactionPartLowDebt($part);
            }

            $this->updateTransactionPart($part, $transactionPartUpdateDataSet);
        }

        return $collection;
    }
}
