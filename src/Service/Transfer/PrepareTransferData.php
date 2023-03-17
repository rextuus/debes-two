<?php

namespace App\Service\Transfer;

use App\Entity\PaymentOption;

/**
 * PrepareTransferData
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class PrepareTransferData
{
    /**
     * @var PaymentOption
     */
    private $paymentOption;

    /**
     * @return PaymentOption
     */
    public function getPaymentOption(): PaymentOption
    {
        return $this->paymentOption;
    }

    /**
     * @param PaymentOption $paymentOption
     */
    public function setPaymentOption(PaymentOption $paymentOption): void
    {
        $this->paymentOption = $paymentOption;
    }
}