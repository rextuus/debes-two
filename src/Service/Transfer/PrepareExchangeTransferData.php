<?php

namespace App\Service\Transfer;

use App\Entity\Loan;

/**
 * PrepareExchangeTransferData
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class PrepareExchangeTransferData
{
    /**
     * @var Loan|null
     */
    private $loan;

    /**
     * @return Loan|null
     */
    public function getLoan(): ?Loan
    {
        return $this->loan;
    }

    /**
     * @param Loan|null $loan
     */
    public function setLoan(?Loan $loan): void
    {
        $this->loan = $loan;
    }
}