<?php

namespace App\Tests;

use App\Entity\BankAccount;
use App\Entity\Debt;
use App\Entity\PaymentAction;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;
use App\Service\PaymentAction\PaymentActionService;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transfer\TransferService;

class TransferServiceTest extends FixtureTestCase
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
                __DIR__ . '/transactions.yml',
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


    public function testCreatePaymentActionByBankAccount(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('transactionExchange1');
        /** @var Debt $debt */
        $debt = $this->getFixtureEntityByIdent('debt1');
        /** @var BankAccount $provider */
        $provider = $this->getFixtureEntityByIdent('bank_account_user_1');
        /** @var BankAccount $receiver */
        $receiver = $this->getFixtureEntityByIdent('bank_account_user_2');

        // check payment actions before
        $paymentActionsBefore = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(0, $paymentActionsBefore);

        // check eevents
        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);
        $this->assertCount(0, $changeEvents);

        $this->transferService->createPaymentActionByPaymentOption($transaction, $provider, $receiver, $debt, PaymentAction::VARIANT_BANK);

        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt->getState());

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // check event creation
        $paymentActionsAfter = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(1, $paymentActionsAfter);
        $this->assertEquals(PaymentAction::VARIANT_BANK, $paymentActionsAfter[0]->getVariant());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[0]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getState());
    }

    public function testCreatePaymentActionByPaypalAccount(): void
    {

        /** @var Transaction $transaction */
        $transaction = $this->getFixtureEntityByIdent('transactionExchange1');
        /** @var Debt $debt */
        $debt = $this->getFixtureEntityByIdent('debt1');
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

        $changeEvents = $this->transactionChangeEventService->getAllByTransaction($transaction);

        $this->assertCount(1, $changeEvents);
        $this->assertEquals($transaction, $changeEvents[0]->getTransaction());

        // check event creation
        $paymentActionsAfter = $this->paymentActionService->getPaymentActionsByProvider($provider->getOwner());
        $this->assertCount(1, $paymentActionsAfter);
        $this->assertEquals(PaymentAction::VARIANT_PAYPAL, $paymentActionsAfter[0]->getVariant());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getDebts()[0]->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $transaction->getState());
    }
}
