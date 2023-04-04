<?php

namespace App\Extension\NextStateProvider;

use App\Service\Transaction\TransactionDtos\TransactionDto;

interface NextStateInterface
{
    public function getTwigParameters(TransactionDto $part): array;
    public function getName(): string;
}
