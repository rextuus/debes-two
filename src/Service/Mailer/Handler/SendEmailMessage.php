<?php

namespace App\Service\Mailer\Handler;

use App\Entity\PaymentAction;
use App\Entity\Transaction;

class SendEmailMessage
{
     private string $mailVariant;
     private Transaction $transaction;
     private PaymentAction|null $paymentAction = null;

    public function __construct(string $mailVariant, Transaction $transaction)
    {
        $this->mailVariant = $mailVariant;
        $this->transaction = $transaction;
    }

    public function getMailVariant(): string
    {
        return $this->mailVariant;
    }

    public function setMailVariant(string $mailVariant): SendEmailMessage
    {
        $this->mailVariant = $mailVariant;
        return $this;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): SendEmailMessage
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getPaymentAction(): ?PaymentAction
    {
        return $this->paymentAction;
    }

    public function setPaymentAction(?PaymentAction $paymentAction): SendEmailMessage
    {
        $this->paymentAction = $paymentAction;
        return $this;
    }
}
