<?php

namespace App\Service\Transaction;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Service\Debt\DebtDto;
use App\Service\Exchange\ExchangeDto;
use App\Service\Exchange\ExchangeService;
use App\Service\Loan\LoanDto;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;

class DtoProvider
{
    public function __construct(
        private ExchangeService $exchangeService,
        private TransactionChangeEventService $changeEventService
    )
    {
    }

    public function createDebtDto(Debt $debt): DebtDto
    {
        $debtDto = DebtDto::create($debt);
        $exchangeDtos = array();

        $debtDto->setExchangeDtos($exchangeDtos);
        return $debtDto;
    }

    public function createLoanDto(Loan $loan): LoanDto
    {
        $loanDto = LoanDto::create($loan);
        $exchangeDtos = [];

        $loanDto->setExchangeDtos($exchangeDtos);
        return $loanDto;
    }

    public function createTransactionDto(Transaction $transaction, TransactionVariant $variant): Dto\TransactionDto
    {
        $transactionDto = \App\Service\Transaction\Dto\TransactionDto::createFromTransaction(
            $transaction,
            $variant
        );


        $exchanges = $this->exchangeService->getAllExchangesBelongingToTransaction($transaction);
        $exchangeDtos = [];
        foreach ($exchanges as $exchange) {
            $corresponding = $this->exchangeService->getCorrespondingExchangeTransaction($exchange);
            $exchangeDtos[] = ExchangeDto::create($exchange, $corresponding);

        }
        $transactionDto->setExchangeDtos($exchangeDtos);

        $changeEvents = $this->changeEventService->getAllByTransaction($transaction);
        $transactionDto->setChangeEvents($changeEvents);

        return $transactionDto;
    }
}