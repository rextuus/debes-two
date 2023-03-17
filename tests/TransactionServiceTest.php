<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\DebtRepository;
use App\Repository\LoanRepository;
use App\Repository\TransactionRepository;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionService;

/**
 * TransactionServiceTest
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class TransactionServiceTest extends FixtureTestCase
{

    /**
     * @var TransactionService
     */
    private $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/users.yml',
                __DIR__ . '/transactions.yml',
                __DIR__ . '/bank_accounts.yml',
            ]
        );
        $this->transactionService = $this->getService(TransactionService::class);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }


    public function testCreateSimpleTransaction(): void
    {
        $transactionRepository = $this->getService(TransactionRepository::class);
        $debtRepository = $this->getService(DebtRepository::class);
        $loanRepository = $this->getService(LoanRepository::class);
        /**
         * @var TransactionChangeEventService
         */
        $changeEventService = $this->getService(TransactionChangeEventService::class);

        $transactionsBefore = count($transactionRepository->findAll());
        $debtsBefore = count($debtRepository->findAll());
        $loansBefore = count($loanRepository->findAll());

        $amount = 19.48;

        /** @var User $requester */
        $requester = $this->getFixtureEntityByIdent('user1');

        /** @var User $debtor */
        $debtor = $this->getFixtureEntityByIdent('user2');

        $data = new TransactionCreateData();
        $data->setAmount($amount);
        $data->setReason('some testing reason');
        $data->setOwner($debtor);
        $data->setLoaners(1);
        $data->setDebtors(1);

        $newTransaction = $this->transactionService->storeSingleTransaction($data, $requester);
        $this->assertEquals('some testing reason', $newTransaction->getReason());
        $this->assertEquals($amount, $newTransaction->getAmount());
        $this->assertEquals($debtor, $newTransaction->getDebtors()[0]);
        $this->assertEquals($requester, $newTransaction->getLoaners()[0]);

        $transactionsAfter = count($transactionRepository->findAll());
        $debtsAfter = count($debtRepository->findAll());
        $loansAfter = count($loanRepository->findAll());

        $this->assertEquals($transactionsBefore+1, $transactionsAfter);
        $this->assertEquals($debtsBefore+1, $debtsAfter);
        $this->assertEquals($loansBefore+1, $loansAfter);
    }
}
