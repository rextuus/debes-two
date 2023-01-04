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
use App\Service\PaymentAction\PaymentActionData;
use App\Service\PaymentAction\PaymentActionService;
use App\Service\PaymentOption\PaymentOptionService;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transaction\TransactionService;
use Exception;

/**
 * TransferService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
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

    /**
     * prepareOptions
     *
     * @param Transaction $transaction
     *
     * @return array
     * @throws Exception
     */
    public function getAvailablePaymentMethodsForTransaction(Transaction $transaction): array
    {
        $debtor = $transaction->getDebts()[0]->getOwner();
        $loaner = $transaction->getLoans()[0]->getOwner();

        $includeBank = !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $debtor,
                true,
                false
            ))
            && !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $loaner,
                true,
                false
            ));
        $includePaypal = !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $debtor,
                false))
            && !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $loaner,
                false)
            );

        if (!$includeBank && !$includePaypal) {
            throw new Exception('There are no matching payment methods for both users');
        }

        $candidates = $this->paymentOptionService->getActivePaymentOptionsOfUser($debtor, $includeBank, $includePaypal);
        $choices = array();
        foreach ($candidates as $candidate) {
            /** @var PaymentOption $candidate */
            $choices[$candidate->getDescription()] = $candidate;
        }
        return $choices;
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

        $this->mailService->sendNotificationMail(
            $transaction,
            MailService::MAIL_DEBT_PAYED_ACCOUNT,
            $paymentAction
        );
    }
}