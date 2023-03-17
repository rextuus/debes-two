<?php

namespace App\Service\PaymentOption;

use App\Entity\PaymentOption;
use App\Entity\User;
use App\Repository\BankAccountRepository;
use App\Repository\PaypalAccountRepository;
use App\Service\Transfer\PaymentOptionSummaryContainer;

class PaymentOptionService
{
    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;

    /**
     * @var BankAccountRepository
     */
    private $bankAccountRepository;

    /**
     * PaymentOptionService constructor.
     *
     * @param PaypalAccountRepository $paypalAccountRepository
     * @param BankAccountRepository $bankAccountRepository
     */
    public function __construct(
        PaypalAccountRepository $paypalAccountRepository,
        BankAccountRepository   $bankAccountRepository
    )
    {
        $this->paypalAccountRepository = $paypalAccountRepository;
        $this->bankAccountRepository = $bankAccountRepository;
    }

    /**
     * getPaymentOptionsOfUser
     *
     * @param User $user
     *
     * @return array
     */
    public function getPaymentOptionsOfUser(User $user): array
    {
    }

    public function getActivePaymentOptionsOfUser(User $loaner, User $debtor, bool $active = false): PaymentOptionSummaryContainer
    {
        $summary = new PaymentOptionSummaryContainer();
        $summary->setAvailableBankAccountsLoaner(
            $this->bankAccountRepository->findBy(['owner' => $loaner, 'enabled' => true])
        );
        $summary->setPreferredBankAccountLoaner(
            $this->bankAccountRepository->findOneBy(['owner' => $loaner, 'enabled' => true, 'isPrioritised' => true])
        );

        $summary->setAvailableBankAccountsDebtor(
            $this->bankAccountRepository->findBy(['owner' => $debtor, 'enabled' => true])
        );
        $summary->setPreferredBankAccountDebtor(
            $this->bankAccountRepository->findOneBy(['owner' => $debtor, 'enabled' => true, 'isPrioritised' => true])
        );


        $summary->setAvailablePaypalAccountsLoaner(
            $this->paypalAccountRepository->findBy(['owner' => $loaner, 'enabled' => true])
        );
        $summary->setPreferredPaypalAccountLoaner(
            $this->paypalAccountRepository->findOneBy(['owner' => $loaner, 'enabled' => true, 'isPrioritised' => true])
        );

        $summary->setAvailablePaypalAccountsDebtor(
            $this->paypalAccountRepository->findBy(['owner' => $debtor, 'enabled' => true])
        );
        $summary->setPreferredPaypalAccountDebtor(
            $this->paypalAccountRepository->findOneBy(['owner' => $debtor, 'enabled' => true, 'isPrioritised' => true])
        );

        return $summary;
    }

    /**
     * getDefaultPaymentOptionForUser
     *
     * @param User $user
     *
     * @return PaymentOption
     */
    public function getDefaultPaymentOptionForUser(User $user): PaymentOption
    {
        $default = $this->bankAccountRepository->findOneBy(
            [
                'owner' => $user,
                'enabled' => true,
                'isPrioritised' => true,
            ]
        );

        if (!$default) {
            $default = $this->paypalAccountRepository->findOneBy(
                [
                    'owner' => $user,
                    'enabled' => true,
                    'isPrioritised' => true,
                ]
            );
        }

        if (!$default) {
            $default = $this->bankAccountRepository->findOneBy(
                [
                    'owner' => $user,
                    'enabled' => true
                ]
            );
        }

        if (!$default) {
            $default = $this->paypalAccountRepository->findOneBy(
                [
                    'owner' => $user,
                    'enabled' => true
                ]
            );
        }
        return $default;
    }
}