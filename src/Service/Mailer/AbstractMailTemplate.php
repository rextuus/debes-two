<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use App\Entity\Transaction;
use App\Service\Transaction\TransactionService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class AbstractMailTemplate
{
//    protected const BASE_URL = 'https://debes.wh-company.de';
    protected const BASE_URL = 'http://localhost:8000';
    protected const INTERACTOR_LOANER_VARIANT = 'Gläubiger';
    protected const INTERACTOR_DEBTOR_VARIANT = 'Schuldner';

    protected Transaction $transaction;

    public function __construct(protected UrlGeneratorInterface $router, protected TransactionService $transactionService) { }


    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): AbstractMailTemplate
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getDetailText(): ?string
    {
        return null;
    }

    public function getHandleLink(): ?string
    {
        return null;
    }
}