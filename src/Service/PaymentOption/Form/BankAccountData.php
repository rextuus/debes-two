<?php

namespace App\Service\PaymentOption\Form;

class BankAccountData extends PaymentOptionData
{

    private string $iban;

    private string $bic;

    private string $accountName;

    private string $bankName;

    public function getIban(): string
    {
        return $this->iban;
    }

    public function setIban(string $iban): BankAccountData
    {
        $this->iban = $iban;
        return $this;
    }

    public function getBic(): string
    {
        return $this->bic;
    }

    public function setBic(string $bic): BankAccountData
    {
        $this->bic = $bic;
        return $this;
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function setAccountName(string $accountName): BankAccountData
    {
        $this->accountName = $accountName;
        return $this;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): BankAccountData
    {
        $this->bankName = $bankName;
        return $this;
    }
}
