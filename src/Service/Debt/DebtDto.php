<?php

namespace App\Service\Debt;

use App\Entity\Debt;
use App\Service\Transaction\LoanAndDebtDto;

class DebtDto extends LoanAndDebtDto
{

    public static function create(Debt $debt): DebtDto
    {
        $dto = new self();

        if ($debt->getTransaction()->hasMultipleDebtors()) {
            $dto->setTransactionPartners('Mehrere Gläubiger');
            $infos = [];
            foreach ($debt->getTransaction()->getLoans() as $loan) {
                $info = sprintf(
                    "%s:\t%.2f €",
                    $loan->getOwner()->getFullName(),
                    $loan->getInitialAmount()
                );
                $infos[] = $info;
            }
            $dto->setTransactionPartnersDetails($infos);
            $dto->setIsMultiple(true);
        } else {
            $dto->setTransactionPartners($debt->getTransaction()->getLoaner()->getFullName());
            $dto->setIsMultiple(false);
        }

        $dto->setAmount($debt->getAmount());

        parent::init($debt, $dto);

        return $dto;
    }
}