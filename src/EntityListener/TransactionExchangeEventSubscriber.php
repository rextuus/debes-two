<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Transaction;
use App\EntityListener\Event\TransactionExchangeEvent;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionUpdateData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class TransactionExchangeEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private TransactionService $transactionService) { }

    public static function getSubscribedEvents(): array
    {
        return [
            TransactionExchangeEvent::NAME => 'onTransactionExchange',
        ];
    }
    public function onTransactionExchange(TransactionExchangeEvent $event): void
    {
        $exchange = $event->getExchange();
        if ($exchange->getRemainingAmount() <= 0){
            $transaction = $exchange->getTransaction();
            $data = (new TransactionUpdateData())->initFrom($transaction);
            $data->setState(Transaction::STATE_CONFIRMED);
            $this->transactionService->confirmTransaction($transaction);
        }
    }

}