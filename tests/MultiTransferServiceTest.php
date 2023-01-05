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
use App\Service\Transfer\TransferService;

class MultiTransferServiceTest extends FixtureTestCase
{

    /**
     * @var TransferService
     */
    private $transferService;

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
                __DIR__ . '/multi_transactions.yml',
                __DIR__ . '/bank_accounts.yml',
            ]
        );
        $this->transferService = $this->getService(TransferService::class);
        $this->transactionChangeEventService = $this->getService(TransactionChangeEventService::class);
        $this->paymentActionService = $this->getService(PaymentActionService::class);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }

    public function testCreatePaymentActionByPaypalAccountMultiLoanSingleDebt(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersSingleDebtor');
        /** @var Debt $debt */
        $debt = $this->getFixtureEntityByIdent('debt1');
        /** @var Loan $loan1 */
        $loan1 = $this->getFixtureEntityByIdent('loan5');
        /** @var Loan $loan2 */
        $loan2 = $this->getFixtureEntityByIdent('loan6');
        /** @var Loan $loan3 */
        $loan3 = $this->getFixtureEntityByIdent('loan7');

        /** @var PaypalAccount $provider */
        $provider = $this->getFixtureEntityByIdent('paypal_account_user_1');
        /** @var PaypalAccount $receiver */
        $receiver = $this->getFixtureEntityByIdent('paypal_account_user_2');

        // check payment actions before
        $paymentActionsBefore = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(0, $paymentActionsBefore);

        // check events
        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(0, $changeEvents);

        $this->transferService->createPaymentActionByPaymentOption($transaction, $provider, $receiver, $debt, PaymentAction::VARIANT_PAYPAL);

        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $loan1->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $loan2->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $loan3->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // check event creation
        $paymentActionsAfter = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(1, $paymentActionsAfter);
        $this->assertEquals(PaymentAction::VARIANT_PAYPAL, $paymentActionsAfter[0]->getVariant());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[0]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getLoans()[0]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getLoans()[1]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getLoans()[2]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getState());
    }

    public function testCreatePaymentActionByPaypalAccountMultiDebtSingleLoan(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('transactionSingleLoanerMultipleDebtors');
        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt2');
        /** @var Debt $debt2 */
        $debt2 = $this->getFixtureEntityByIdent('debt3');
        /** @var Debt $debt3 */
        $debt3 = $this->getFixtureEntityByIdent('debt4');
        /** @var Loan $loan */
        $loan = $this->getFixtureEntityByIdent('loan1');

        /** @var PaypalAccount $provider */
        $provider = $this->getFixtureEntityByIdent('paypal_account_user_1');
        /** @var PaypalAccount $receiver */
        $receiver = $this->getFixtureEntityByIdent('paypal_account_user_2');

        // # nr 1
        // check payment actions before
        $paymentActionsBefore = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(0, $paymentActionsBefore);

        // check events
        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(0, $changeEvents);

        $this->transferService->createPaymentActionByPaymentOption($transaction, $provider, $receiver, $debt1, PaymentAction::VARIANT_PAYPAL);

        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt3->getState());
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $loan->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // check event creation
        $paymentActionsAfter = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(1, $paymentActionsAfter);
        $this->assertEquals(PaymentAction::VARIANT_PAYPAL, $paymentActionsAfter[0]->getVariant());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[0]->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transaction->getDebts()[1]->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transaction->getDebts()[2]->getState());
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $transaction->getLoans()[0]->getState());
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $transaction->getState());

        // # nr 2
        // check payment actions before
        $paymentActionsBefore = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(1, $paymentActionsBefore);

        // check events
        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(1, $changeEvents);

        $this->transferService->createPaymentActionByPaymentOption($transaction, $provider, $receiver, $debt3, PaymentAction::VARIANT_PAYPAL);

        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt3->getState());
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $loan->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // check event creation
        $paymentActionsAfter = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(2, $paymentActionsAfter);
        $this->assertEquals(PaymentAction::VARIANT_PAYPAL, $paymentActionsAfter[0]->getVariant());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[0]->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transaction->getDebts()[1]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[2]->getState());
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $transaction->getLoans()[0]->getState());
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $transaction->getState());

        // # nr 3
        // check payment actions before
        $paymentActionsBefore = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(2, $paymentActionsBefore);

        // check events
        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(1, $changeEvents);

        $this->transferService->createPaymentActionByPaymentOption($transaction, $provider, $receiver, $debt2, PaymentAction::VARIANT_PAYPAL);

        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt2->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt3->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $loan->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(2, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // check event creation
        $paymentActionsAfter = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(3, $paymentActionsAfter);
        $this->assertEquals(PaymentAction::VARIANT_PAYPAL, $paymentActionsAfter[0]->getVariant());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[0]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[1]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[2]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getLoans()[0]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getState());
    }
}
