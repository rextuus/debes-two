<?php

namespace App\Tests;

use App\Entity\BankAccount;
use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\PaymentAction;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;
use App\Service\PaymentAction\PaymentActionService;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transfer\TransferService;

class TransactionProcessorTest extends FixtureTestCase
{

    /**
     * @var TransactionProcessor
     */
    private $transactionProcessor;

    /**
     * @var TransactionChangeEventService
     */
    private $transactionChangeEventService;

    /**
     * @var PaymentActionService
     */
    private $paymentActionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/users.yml',
                __DIR__ . '/multi_transactions_non_accepted.yml',
                __DIR__ . '/bank_accounts.yml',
            ]
        );
        $this->transactionProcessor = $this->getService(TransactionProcessor::class);
        $this->transactionChangeEventService = $this->getService(TransactionChangeEventService::class);
        $this->paymentActionService = $this->getService(PaymentActionService::class);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }


    public function testAcceptTransactionSingleLoanMultipleDebts(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('transactionSingleLoanerMultipleDebtors');

        /** @var Loan $loan1 */
        $loan1 = $this->getFixtureEntityByIdent('loan1');

        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt2');
        /** @var Debt $debt2 */
        $debt2 = $this->getFixtureEntityByIdent('debt3');
        /** @var Debt $debt3 */
        $debt3 = $this->getFixtureEntityByIdent('debt4');

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(0, $changeEvents);

        $this->assertEquals(Transaction::STATE_READY, $transaction->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt1->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt2->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt3->getState());

        // #1
        $this->transactionProcessor->accept($debt1);

        $this->assertEquals(Transaction::STATE_PARTIAL_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt2->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt3->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // #2
        $this->transactionProcessor->accept($debt3);

        $this->assertEquals(Transaction::STATE_PARTIAL_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt2->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt3->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // #3
        $this->transactionProcessor->accept($debt2);

        $this->assertEquals(Transaction::STATE_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt3->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(2, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());
        $this->assertEquals($transaction, $changeEvents[1]->getTransaction());
    }

    public function testAcceptTransactionSingleDebtMultipleLoan(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersSingleDebtor');

        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt1');

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(0, $changeEvents);

        $this->assertEquals(Transaction::STATE_READY, $transaction->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt1->getState());

        // #1
        $this->transactionProcessor->accept($debt1);

        $this->assertEquals(Transaction::STATE_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());
    }

    public function testAcceptTransactionMultipleLoanMultipleDebts(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors');

        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt5');
        /** @var Debt $debt2 */
        $debt2 = $this->getFixtureEntityByIdent('debt6');
        /** @var Debt $debt3 */
        $debt3 = $this->getFixtureEntityByIdent('debt7');

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(0, $changeEvents);

        $this->assertEquals(Transaction::STATE_READY, $transaction->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt1->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt2->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt3->getState());

        // #1
        $this->transactionProcessor->accept($debt1);

        $this->assertEquals(Transaction::STATE_PARTIAL_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt2->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt3->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // #2
        $this->transactionProcessor->accept($debt3);

        $this->assertEquals(Transaction::STATE_PARTIAL_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt2->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt3->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // #3
        $this->transactionProcessor->accept($debt2);

        $this->assertEquals(Transaction::STATE_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt3->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(2, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());
        $this->assertEquals($transaction, $changeEvents[1]->getTransaction());
    }

    public function testAcceptTransactionSingleVariant(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('singleTransactionLow');

        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt5_2');

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(0, $changeEvents);

        $this->assertEquals(Transaction::STATE_READY, $transaction->getState());
        $this->assertEquals(Transaction::STATE_READY, $debt1->getState());

        // #1
        $this->transactionProcessor->accept($debt1);

        $this->assertEquals(Transaction::STATE_ACCEPTED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());
    }
}
