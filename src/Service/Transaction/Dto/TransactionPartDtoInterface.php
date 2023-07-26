<?php

namespace App\Service\Transaction\Dto;

interface TransactionPartDtoInterface
{
    public function getAmount(): float;

    public function getReason(): float;
}