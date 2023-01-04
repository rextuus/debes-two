<?php

namespace App\Service\Transaction\ChangeEvent;

use App\Entity\Transaction;
use App\Entity\TransactionStateChangeEvent;
use App\Entity\User;
use App\Repository\TransactionStateChangeEventRepository;

class TransactionChangeEventService
{
    private TransactionChangeEventFactory $changeEventFactory;
    private TransactionStateChangeEventRepository $repository;

    public function __construct(
        TransactionChangeEventFactory         $changeEventFactory,
        TransactionStateChangeEventRepository $repository
    )
    {
        $this->changeEventFactory = $changeEventFactory;
        $this->repository = $repository;
    }

    public function storeTransactionChangeEvent(TransactionChangeEventData $transactionChangeEventData): TransactionStateChangeEvent
    {
        $transactionStateChangeEvent = $this->changeEventFactory->createByData($transactionChangeEventData);

        $this->repository->persist($transactionStateChangeEvent);

        return $transactionStateChangeEvent;
    }

    /**
     * @return TransactionStateChangeEvent[]
     */
    public function getAllByTransaction(Transaction $transaction): array
    {
        return $this->repository->findBy(['transaction' => $transaction->getId()]);
    }

    /**
     * @return TransactionStateChangeEvent[]
     */
    public function getAllByUser(User $user, array $filter = []): array
    {
        return $this->repository->findAllEventsForUser($user);
    }
}