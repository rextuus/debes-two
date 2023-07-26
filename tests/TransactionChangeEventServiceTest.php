<?php

namespace App\Tests;

use App\Entity\User;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;

/**
 * TransactionServiceTest
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
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
        $this->assertCount(2, $events);
    }
}
