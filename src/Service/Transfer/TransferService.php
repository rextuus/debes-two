<?php

namespace App\Service\Transfer;

use App\Entity\BankAccount;
use App\Entity\Debt;
use App\Entity\PaymentAction;
use App\Entity\PaymentOption;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Mailer\MailService;
use App\Service\PaymentAction\Form\PaymentActionData;
use App\Service\PaymentAction\PaymentActionService;
use App\Service\PaymentOption\PaymentOptionService;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transaction\TransactionService;

/**
 * TransferService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class TransferService
{

    /**
     * TransferService constructor.
     */
    public function __construct(
        private PaymentOptionService $paymentOptionService,
        private TransactionService   $transactionService,
        private PaymentActionService $paymentActionService,
        private TransactionProcessor $transactionProcessor,
        private MailService          $mailService,
    )
    {
    }

    /**
     * getDefaultPaymentOptionForUser
     * @param User $user
     *
     * @return PaymentOption
     */
    public function getDefaultPaymentOptionForUser(User $user): PaymentOption
    {
        return $this->paymentOptionService->getDefaultPaymentOptionForUser($user);
    }

    public function getAvailablePaymentMethodsForTransaction(Transaction $transaction): PaymentOptionSummaryContainer
    {
        return $this->paymentOptionService->getActivePaymentOptionsOfUser(
            $transaction->getLoaner(),
            $transaction->getDebtor()
        );
    }

    public function createPaymentActionByPaymentOption(
        Transaction   $transaction,
        PaymentOption $senderBankAccount,
        PaymentOption $receiverBankAccount,
        Debt          $debt,
        string        $variant
    ): void
    {
        $paymentActionData = new PaymentActionData();
        $paymentActionData->setTransaction($transaction);
        $paymentActionData->setVariant($variant);

        if ($variant === PaymentAction::VARIANT_BANK && $senderBankAccount instanceof BankAccount && $receiverBankAccount instanceof BankAccount) {
            $paymentActionData->setBankAccountSender($senderBankAccount);
            $paymentActionData->setBankAccountReceiver($receiverBankAccount);
        }

        if ($variant === PaymentAction::VARIANT_PAYPAL && $senderBankAccount instanceof PaypalAccount && $receiverBankAccount instanceof PaypalAccount) {
            $paymentActionData->setPaypalAccountSender($senderBankAccount);
            $paymentActionData->setPaypalAccountReceiver($senderBankAccount);
        }

        $paymentAction = $this->paymentActionService->storePaymentAction($paymentActionData);

//        $transactionUpdateData = (new TransactionUpdateData())->initFrom($transaction);
//        $transactionUpdateData->setState(Transaction::STATE_CLEARED);
//        $transactionUpdateData->setChangeType(TransactionStateChangeEvent::TYPE_BANK_ACCOUNT);
//        $transactionUpdateData->setTarget($paymentAction);
//        $this->transactionService->update($transaction, $transactionUpdateData);
        $this->transactionProcessor->process($debt);

        $mailVariant = MailService::MAIL_DEBT_PAYED_ACCOUNT;
        if ($paymentAction->getVariant() === PaymentAction::VARIANT_PAYPAL){
            $mailVariant = MailService::MAIL_DEBT_PAYED_PAYPAL;
        }
        $this->mailService->sendNotificationMail(
            $transaction,
            $mailVariant,
            $paymentAction
        );
    }
}