<?php

namespace App\Service\Exchange;

use App\Entity\Exchange;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\EntityListener\Event\TransactionExchangeEvent;
use App\Repository\ExchangeRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * ExchangeService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class ExchangeService
{

    public function __construct(
        private ExchangeFactory    $exchangeFactory,
        private ExchangeRepository $exchangeRepository,
    )
    {
    }

    /**
     * storeExchange
     *
     * @param ExchangeCreateData $exchangeData
     * @param bool $persist
     *
     * @return Exchange
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeExchange(ExchangeCreateData $exchangeData, bool $persist = true): Exchange
    {
        $exchange = $this->exchangeFactory->createByData($exchangeData);

        if ($persist) {
            $this->exchangeRepository->persist($exchange);
        }

        return $exchange;
    }

    /**
     * update
     *
     * @param Exchange $transaction
     * @param ExchangeUpdateData $data
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Exchange $transaction, ExchangeUpdateData $data): void
    {
        $this->exchangeFactory->mapData($transaction, $data);

        $this->exchangeRepository->persist($transaction);
    }

    /**
     * getAllExchangesBelongingToTransaction
     *
     * @param Transaction $transaction
     *
     * @return Exchange[]
     *
     */
    public function getAllExchangesBelongingToTransaction(Transaction $transaction): array
    {
        return $this->exchangeRepository->findBy(['transaction' => $transaction]);
    }

    /**
     * @param Exchange $exchange
     * @return Transaction
     */
    public function getCorrespondingExchangeTransaction(Exchange $exchange): Transaction
    {
        return $this->exchangeRepository->findCorrespondingExchange(
            $exchange->getTransaction(),
            $exchange->getDebt(),
            $exchange->getLoan()
        );
    }


    /**
     * getAllExchangesBelongingToTransactionAndPartType
     *
     * @param Transaction $transaction
     * @param TransactionPartInterface $transactionPart
     * @param bool $isDebt
     *
     * @return Exchange[]
     */
    public function getAllExchangesBelongingToTransactionAndPartType(
        Transaction              $transaction,
        TransactionPartInterface $transactionPart,
        bool                     $isDebt = true
    ): array
    {
        if ($isDebt) {
            return $this->exchangeRepository->findBy(['transaction' => $transaction, 'loan' => $transactionPart]);
        } else {
            return $this->exchangeRepository->findBy(['transaction' => $transaction, 'debt' => $transactionPart]);
        }
    }

    public function findById(int $int)
    {
        return $this->exchangeRepository->find($int);
    }
}
