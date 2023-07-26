<?php

namespace App\Service\PaymentAction\Form;

use App\Entity\BankAccount;
use App\Entity\Exchange;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;

class PaymentActionData
{
    private Transaction $transaction;

    private string $variant;

    private ?Exchange $exchange = null;

    private ?BankAccount $bankAccountSender = null;

    private ?PaypalAccount $paypalAccountSender = null;

    private ?BankAccount $bankAccountReceiver = null;

    private ?PaypalAccount $paypalAccountReceiver = null;

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): PaymentActionData
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getVariant(): string
    {
        return $this->variant;
    }

    public function setVariant(string $variant): PaymentActionData
    {
        $this->variant = $variant;
        return $this;
    }

    public function getExchange(): ?Exchange
    {
        return $this->exchange;
    }

    public function setExchange(?Exchange $exchange): PaymentActionData
    {
        $this->exchange = $exchange;
        return $this;
    }

    public function getBankAccountSender(): ?BankAccount
    {
        return $this->bankAccountSender;
    }

    public function setBankAccountSender(?BankAccount $bankAccountSender): PaymentActionData
    {
        $this->bankAccountSender = $bankAccountSender;
        return $this;
    }

    public function getPaypalAccountSender(): ?PaypalAccount
    {
        return $this->paypalAccountSender;
    }

    public function setPaypalAccountSender(?PaypalAccount $paypalAccountSender): PaymentActionData
    {
        $this->paypalAccountSender = $paypalAccountSender;
        return $this;
    }

    public function getBankAccountReceiver(): ?BankAccount
    {
        return $this->bankAccountReceiver;
    }

    public function setBankAccountReceiver(?BankAccount $bankAccountReceiver): PaymentActionData
    {
        $this->bankAccountReceiver = $bankAccountReceiver;
        return $this;
    }

    public function getPaypalAccountReceiver(): ?PaypalAccount
    {
        return $this->paypalAccountReceiver;
    }

    public function setPaypalAccountReceiver(?PaypalAccount $paypalAccountReceiver): PaymentActionData
    {
        $this->paypalAccountReceiver = $paypalAccountReceiver;
        return $this;
    }
}