<?php
declare(strict_types=1);

namespace App\Service\Transfer;

use App\Entity\BankAccount;
use App\Entity\PaypalAccount;


class PaymentOptionSummaryContainer
{
    /**
     * @var BankAccount[]
     */
    private array $availableBankAccountsLoaner;

    /**
     * @var BankAccount[]
     */
    private array $availableBankAccountsDebtor;

    /**
     * @var PaypalAccount[]
     */
    private array $availablePaypalAccountsLoaner;

    /**
     * @var PaypalAccount[]
     */
    private array $availablePaypalAccountsDebtor;

    private ?BankAccount $preferredBankAccountLoaner;
    private ?BankAccount $preferredBankAccountDebtor;
    private ?PaypalAccount $preferredPaypalAccountDebtor;
    private ?PaypalAccount $preferredPaypalAccountLoaner;

    public function getAvailableBankAccountsLoaner(): array
    {
        return $this->availableBankAccountsLoaner;
    }

    public function setAvailableBankAccountsLoaner(array $availableBankAccountsLoaner): void
    {
        $this->availableBankAccountsLoaner = $availableBankAccountsLoaner;
    }

    public function getAvailableBankAccountsDebtor(): array
    {
        return $this->availableBankAccountsDebtor;
    }

    public function setAvailableBankAccountsDebtor(array $availableBankAccountsDebtor): void
    {
        $this->availableBankAccountsDebtor = $availableBankAccountsDebtor;
    }

    public function getAvailablePaypalAccountsLoaner(): array
    {
        return $this->availablePaypalAccountsLoaner;
    }

    public function setAvailablePaypalAccountsLoaner(array $availablePaypalAccountsLoaner): void
    {
        $this->availablePaypalAccountsLoaner = $availablePaypalAccountsLoaner;
    }

    public function getAvailablePaypalAccountsDebtor(): array
    {
        return $this->availablePaypalAccountsDebtor;
    }

    public function setAvailablePaypalAccountsDebtor(array $availablePaypalAccountsDebtor): void
    {
        $this->availablePaypalAccountsDebtor = $availablePaypalAccountsDebtor;
    }

    public function getPreferredBankAccountLoaner(): ?BankAccount
    {
        return $this->preferredBankAccountLoaner;
    }

    public function setPreferredBankAccountLoaner(?BankAccount $preferredBankAccountLoaner): void
    {
        $this->preferredBankAccountLoaner = $preferredBankAccountLoaner;
    }

    public function getPreferredBankAccountDebtor(): ?BankAccount
    {
        return $this->preferredBankAccountDebtor;
    }

    public function setPreferredBankAccountDebtor(?BankAccount $preferredBankAccountDebtor): void
    {
        $this->preferredBankAccountDebtor = $preferredBankAccountDebtor;
    }

    public function getPreferredPaypalAccountDebtor(): ?PaypalAccount
    {
        return $this->preferredPaypalAccountDebtor;
    }

    public function setPreferredPaypalAccountDebtor(?PaypalAccount $preferredPaypalAccountDebtor): void
    {
        $this->preferredPaypalAccountDebtor = $preferredPaypalAccountDebtor;
    }

    public function getPreferredPaypalAccountLoaner(): ?PaypalAccount
    {
        return $this->preferredPaypalAccountLoaner;
    }

    public function setPreferredPaypalAccountLoaner(?PaypalAccount $preferredPaypalAccountLoaner): void
    {
        $this->preferredPaypalAccountLoaner = $preferredPaypalAccountLoaner;
    }

    public function loanerHasBankOptions(): bool
    {
        return (bool)count($this->availableBankAccountsLoaner);
    }

    public function debtorHasBankOptions(): bool
    {
        return (bool)count($this->availableBankAccountsDebtor);
    }

    public function bothHaveBankOptions(): bool
    {
        return $this->debtorHasBankOptions() && $this->loanerHasBankOptions();
    }

    public function bothHavePaypalOptions(): bool
    {
        return $this->debtorHasPaypalOptions() && $this->loanerHasPaypalOptions();
    }

    public function loanerHasPaypalOptions(): bool
    {
        return (bool)count($this->availablePaypalAccountsLoaner);
    }

    public function debtorHasPaypalOptions(): bool
    {
        return (bool)count($this->availablePaypalAccountsDebtor);
    }
}