<?php

namespace App\Tests;

use App\Entity\Debt;
use App\Entity\User;
use App\Repository\DebtRepository;
use App\Repository\LoanRepository;
use App\Repository\TransactionRepository;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionService;

/**
 * TransactionServiceTest
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionChangeEventServiceTest extends FixtureTestCase
{

    /**
     * @var TransactionChangeEventService
     */
    private $transactionChangeEventService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/users.yml',
                __DIR__ . '/multi_transactions.yml',
                __DIR__ . '/bank_accounts.yml',
                __DIR__ . '/transaction_change_event.yml',
            ]
        );
        $this->transactionChangeEventService = $this->getService(TransactionChangeEventService::class);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }


    public function testGetAllByUser(): void
    {
        /** @var User $user */
        $user = $this->getFixtureEntityByIdent('exchangeUser1');
        $events = $this->transactionChangeEventService->getAllByUser($user);
        dump($events);
    }
}
