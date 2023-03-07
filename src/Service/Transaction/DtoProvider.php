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
use App\Service\Transaction\TransactionDtos\TransactionPartBaseDto;

/**
 * DtoProvider
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class DtoProvider
{
    public function __construct(
        private ExchangeService $exchangeService,
        private TransactionChangeEventService $changeEventService
    )
    {
    }


    /**
     * createDebtDto
     *
     * @param Debt $debt
     *
     * @return DebtDto
     */
    public function createDebtDto(Debt $debt): DebtDto
    {
        $debtDto = DebtDto::create($debt);
        $exchanges = $this->exchangeService->getAllExchangesBelongingToTransaction($debt->getTransaction());
        $exchangeDtos = array();
//        foreach ($exchanges as $exchange) {
//            $exchangeDtos[] = ExchangeDto::create($exchange);
//        }
        $debtDto->setExchangeDtos($exchangeDtos);
        return $debtDto;
    }

    /**
     * createLoanDto
     *
     * @param Loan $loan
     *
     * @return LoanDto
     */
    public function createLoanDto(Loan $loan): LoanDto
    {
        $loanDto = LoanDto::create($loan);
        $exchanges = $this->exchangeService->getAllExchangesBelongingToTransaction($loan->getTransaction());
        $exchangeDtos = array();
//        foreach ($exchanges as $exchange) {
//            $exchangeDtos[] = ExchangeDto::create($exchange);
//        }
        $loanDto->setExchangeDtos($exchangeDtos);
        return $loanDto;
    }

    /**
     * createDebtDto
     *
     * @param Transaction $transaction
     *
     * @return TransactionDtos\TransactionDto
     */
    public function createTransactionDto(Transaction $transaction, bool $isDebtVariant): TransactionDtos\TransactionDto
    {
        $transactionDto = \App\Service\Transaction\TransactionDtos\TransactionDto::createFromTransaction(
            $transaction,
            $isDebtVariant
        );


        $exchanges = $this->exchangeService->getAllExchangesBelongingToTransaction($transaction);
        $exchangeDtos = [];
        foreach ($exchanges as $exchange) {
            $corresponding = $this->exchangeService->getCorrespondingExchangeTransaction($exchange);
//            dd($corresponding->getReason());
            $exchangeDtos[] = ExchangeDto::create($exchange, $corresponding);

        }
        $transactionDto->setExchangeDtos($exchangeDtos);

        $changeEvents = $this->changeEventService->getAllByTransaction($transaction);
        $transactionDto->setChangeEvents($changeEvents);

        return $transactionDto;
    }
}