<?php

declare(strict_types=1);

namespace App\EntityListener\Event;

use App\Entity\Exchange;
use Symfony\Contracts\EventDispatcher\Event;


class TransactionExchangeEvent extends Event
{
    public const NAME = 'transaction.exchange';

    public function __construct(
        protected Exchange $exchange,
    ) {
    }

    public function getExchange(): Exchange
    {
        return $this->exchange;
    }



}