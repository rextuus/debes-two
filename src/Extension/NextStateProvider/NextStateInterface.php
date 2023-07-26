<?php

namespace App\Extension\NextStateProvider;

use App\Service\Transaction\Dto\TransactionDto;

interface NextStateInterface
{
    public function getTwigParameters(TransactionDto $part): array;
    public function getName(): string;
}
