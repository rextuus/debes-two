<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Calculation;

use App\Entity\GroupEvent;
use App\Entity\Transaction;
use App\Service\GroupEvent\Result\Form\GroupEventResultData;
use App\Service\GroupEvent\Result\GroupEventResultService;
use App\Service\Transaction\Transaction\Form\TransactionCreateData;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transaction\TransactionService;
use App\Service\Transfer\ExchangeProcessor;

class GroupEventCalculator
{
    public function __construct(
        private readonly GroupEventResultService $groupEventResultService,
        private readonly TransactionService $transactionService,
        private readonly TransactionProcessor $transactionProcessor,
        private readonly ExchangeProcessor $exchangeProcessor,
    ) {
    }

    public function calculateGroupEventFinalBill(GroupEvent $event): void
    {
        $cashBoxes = [];

        $allUsers = $event->getUsers();
        foreach ($allUsers as $user) {
            $cashBox = new CashBox();
            $cashBox->setOwner($user);
            $cashBox->setAmount(0.0);
            $cashBox->setPayments([]);

            $cashBoxes[$user->getId()] = $cashBox;
        }

        foreach ($event->getGroupEventPayments() as $payment) {
            $loaner = $payment->getLoaner();
            foreach ($payment->getDebtors()->getUsers() as $debtor) {
                $paymentAction = new Payment();
                $splitAmount = $payment->getAmount() / count($payment->getDebtors()->getUsers());
                $paymentAction->setLoaner($loaner);
                $paymentAction->setDebtor($debtor);
                $paymentAction->setAmount($splitAmount);
                $paymentAction->setReason($payment->getReason());
                $cashBoxes[$debtor->getId()]->addPaymentAction($paymentAction);
            }
        }

        //calculate result
        foreach ($cashBoxes as $cashBox) {
            foreach ($allUsers as $user) {
                if ($user === $cashBox->getOwner()) {
                    continue;
                }

                $paymentsToUser = array_filter($cashBox->getPayments(), function (Payment $payment) use ($user) {
                    return ($user->getId() === $payment->getLoaner()->getId());
                });
                $totalAmount = 0.0;
                $reason = [];
                array_walk($paymentsToUser, function (Payment $payment) use (&$totalAmount, &$reason) {
                    $totalAmount = $totalAmount + $payment->getAmount();
                    $reason[] = sprintf('%s (%.2f €)', $payment->getReason(), $payment->getAmount());
                });

                $result = $this->groupEventResultService->findByEventDebtorLoanerCombination(
                    $event,
                    $cashBox->getOwner(),
                    $user
                );

                if ($totalAmount > 0.0) {
                    if ($result) {
                        $resultData = (new GroupEventResultData())->initFrom($result);
                        $resultData->setReason(implode(',', $reason));
                        $resultData->setAmount($totalAmount);

                        $this->groupEventResultService->update($result, $resultData);
                    } else {
                        $resultData = new GroupEventResultData();
                        $resultData->setEvent($event);
                        $resultData->setReason(implode(',', $reason));
                        $resultData->setAmount($totalAmount);
                        $resultData->setDebtor($cashBox->getOwner());
                        $resultData->setLoaner($user);

                        $this->groupEventResultService->storeGroupEventResult($resultData);
                    }
                }
            }
        }
    }

    public function triggerTransactionCreation(GroupEvent $groupEvent, bool $createExchanges = true): void
    {
        $results = $this->groupEventResultService->findAllForEvent($groupEvent);
        $exchangeMap = [];
        foreach ($results as $result) {
//            dump($result->getDebtor()->getId().'-'.$result->getLoaner()->getId().': '.$result->getAmount());
            $transactionData = new TransactionCreateData();
            $transactionData->setOwner($result->getDebtor());
            $transactionData->setAmount($result->getAmount());
            $transactionData->setReason('Zahlung für das Event: ' . $groupEvent->getDecscription());

            $transaction = $this->transactionService->storeSingleTransaction($transactionData, $result->getLoaner());

            // store possible exchange relations
            $key = $result->getLoaner()->getId() . ':' . $result->getDebtor()->getId();
            $reversedKey = $result->getDebtor()->getId() . ':' . $result->getLoaner()->getId();
            if (array_key_exists($reversedKey, $exchangeMap)) {
                $exchangeMap[$reversedKey][] = $transaction;
            } else {
                $exchangeMap[$key] = [$transaction];
            }
        }

        // exchange automatically
        if ($createExchanges) {
            foreach ($exchangeMap as $combination) {
                if (count($combination) > 1) {
                    // lets instantly exchange the created
                    /** @var Transaction[] $combination */
                    $this->transactionProcessor->accept($combination[0]->getDebts()->get(0));
                    $this->transactionProcessor->accept($combination[1]->getDebts()->get(0));

                    $this->exchangeProcessor->exchangeTransactionParts(
                        $combination[0]->getDebts()->get(0),
                        $combination[1]->getLoans()->get(0)
                    );
                }
            }
        }
    }
}