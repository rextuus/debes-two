<?php

namespace App\Tests\GroupEvent;

use App\Entity\Exchange;
use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\ExchangeRepository;
use App\Repository\TransactionRepository;
use App\Service\GroupEvent\Event\Form\GroupEventInitData;
use App\Service\GroupEvent\Event\GroupEventRepository;
use App\Service\GroupEvent\GroupEventManager;
use App\Service\GroupEvent\Payment\Form\GroupEventPaymentData;
use App\Service\GroupEvent\UserCollection\DisplayName\DisplayNameRepository;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionRepository;
use App\Tests\FixtureTestCase;

/**
 * ExchangeProcessorTest
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 *
 */
class GroupEventServiceTest extends FixtureTestCase
{

    /**
     * @var GroupEventManager
     */
    private $groupEventManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/../group_events.yml',
            ]
        );
        $this->groupEventManager = $this->getService(GroupEventManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGroupEvaluationWithoutExchanges(): void
    {
        /** @var GroupEvent $event */
        $event = $this->getFixtureEntityByIdent('event2');

        // first calc
        $this->groupEventManager->calculateGroupEventFinalBill($event);

        // adding new payment
        /** @var User $event1_participant2 */
        $event1_participant2 = $this->getFixtureEntityByIdent('event1_participant2');

        /** @var GroupEventUserCollection $all */
        $all = $this->getFixtureEntityByIdent('event2_all');

        // Extra payment created 
        $paymentData = new GroupEventPaymentData();
        $paymentData->setGroupEvent($event);
        $paymentData->setLoaner($event1_participant2);
        $paymentData->setAmount(40);
        $paymentData->setDebtors($all);
        $paymentData->setReason('New payment');

        $payment = $this->groupEventManager->addPaymentToEvent($paymentData);

        $this->groupEventManager->calculateGroupEventFinalBill($event);

        $this->groupEventManager->triggerTransactionCreation($event, false);

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->getService(TransactionRepository::class);
        $transactions = $transactionRepository->findAll();

        // C => P3 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[0], 1, 4);
        $this->assertEquals(25.0, $transactions[0]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[0]->getState());

        // P1 => C
        $this->assertCorrectTransactionRelation($transactions[1], 2, 1);
        $this->assertEquals(27.0, $transactions[1]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[1]->getState());

        // P1 => P3
        $this->assertCorrectTransactionRelation($transactions[2], 2, 4);
        $this->assertEquals(25.0, $transactions[2]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[2]->getState());

        // P2 => C
        $this->assertCorrectTransactionRelation($transactions[3], 3, 1);
        $this->assertEquals(27.0, $transactions[3]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[3]->getState());

        // P2 => P3
        $this->assertCorrectTransactionRelation($transactions[4], 3, 4);
        $this->assertEquals(25.0, $transactions[4]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[4]->getState());

        // P3 => C (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[5], 4, 1);
        $this->assertEquals(27.0, $transactions[5]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[5]->getState());

        // C => P2 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[6], 1, 3);
        $this->assertEquals(10.0, $transactions[6]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[6]->getState());

        // P1 => P2 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[7], 2, 3);
        $this->assertEquals(10.0, $transactions[7]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[7]->getState());

        // P3 => P2 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[8], 4, 3);
        $this->assertEquals(10.0, $transactions[8]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[8]->getState());
    }

    public function testGroupEvaluationWithExchanges(): void
    {
        /*
         * Payments (A has paid X for B's)
         * C => all 28 / 4 = 7
         * P3 => all 100 / 4 = 25
         * C => all other 60 / 3 = 20
         * P2 => P2 90 / 1 = 90
         * (extra created during test) P2 => all 40 / 4 = 10
         *
         * resulting transactions before exchanging (A has to pay x to B)
         * C:   7 => himself
         *      10 => P2
         *      25 => P3
         *
         * P1:  27 (7+20) => C
         *      10 => P2
         *      25 => P3
         *
         * P2:  100 (90+10) => himself
         *      27 (7+20) => C
         *      25 => P3
         *
         * P3:  25 => himself
         *      27 (7+20) => C
         *      10 => P2
         *
         * resulting transactions after exchanging
         * C:   7 => himself
         *      0 => P2             I
         *      0 => P3             II
         *
         * P1:  27 (7+20) => C
         *      10 => P2
         *      25 => P3
         *
         * P2:  100 (90+10) => himself
         *      17 (7+20-10) => C   I
         *      15 (-1) => P3       III
         *
         * P3:  25 => himself
         *      2 (7+20-25) => C    II
         *      0 => P2             III
         *
         * resulting exchanges
         * C (10) <=> P2 (27) | 0 <=> 17
         * C (25) <=> P3 (27) | 0 <=> 2
         * P2 (25) <=> P3 (10) | 15 <=> 0
         */

        /** @var GroupEvent $event */
        $event = $this->getFixtureEntityByIdent('event2');

        // first calc
        $this->groupEventManager->calculateGroupEventFinalBill($event);

        // adding new payment
        /** @var User $event1_participant2 */
        $event1_participant2 = $this->getFixtureEntityByIdent('event1_participant2');

        /** @var GroupEventUserCollection $all */
        $all = $this->getFixtureEntityByIdent('event2_all');

        // Extra payment created
        $paymentData = new GroupEventPaymentData();
        $paymentData->setGroupEvent($event);
        $paymentData->setLoaner($event1_participant2);
        $paymentData->setAmount(40);
        $paymentData->setDebtors($all);
        $paymentData->setReason('New payment');

        $this->groupEventManager->addPaymentToEvent($paymentData);

        $this->groupEventManager->calculateGroupEventFinalBill($event);

        $this->groupEventManager->triggerTransactionCreation($event);

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->getService(TransactionRepository::class);
        $transactions = $transactionRepository->findAll();

        // C => P3 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[0], 1, 4);
        $this->assertEquals(0.0, $transactions[0]->getAmount());
        $this->assertEquals(Transaction::STATE_CONFIRMED, $transactions[0]->getState());

        // P1 => C
        $this->assertCorrectTransactionRelation($transactions[1], 2, 1);
        $this->assertEquals(27.0, $transactions[1]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[1]->getState());

        // P1 => P3
        $this->assertCorrectTransactionRelation($transactions[2], 2, 4);
        $this->assertEquals(25.0, $transactions[2]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[2]->getState());

        // P2 => C
        $this->assertCorrectTransactionRelation($transactions[3], 3, 1);
        $this->assertEquals(17.0, $transactions[3]->getAmount());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transactions[3]->getState());

        // P2 => P3
        $this->assertCorrectTransactionRelation($transactions[4], 3, 4);
        $this->assertEquals(15.0, $transactions[4]->getAmount());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transactions[4]->getState());

        // P3 => C (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[5], 4, 1);
        $this->assertEquals(2.0, $transactions[5]->getAmount());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transactions[5]->getState());

        // C => P2 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[6], 1, 3);
        $this->assertEquals(0.0, $transactions[6]->getAmount());
        $this->assertEquals(Transaction::STATE_CONFIRMED, $transactions[6]->getState());

        // P1 => P2 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[7], 2, 3);
        $this->assertEquals(10.0, $transactions[7]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[7]->getState());

        // P3 => P2 (be aware it was automatically exchanged)
        $this->assertCorrectTransactionRelation($transactions[8], 4, 3);
        $this->assertEquals(0.0, $transactions[8]->getAmount());
        $this->assertEquals(Transaction::STATE_CONFIRMED, $transactions[8]->getState());

        /** @var ExchangeRepository $exchangeRepository */
        $exchangeRepository = $this->getService(ExchangeRepository::class);
        $exchanges = $exchangeRepository->findAll();
        $this->assertCount(6, $exchanges);

        // Exchange of C => P3: 27 - 25 ~> 2
        $this->assertCorrectExchangeRelation($exchanges[0], 1, 4);
        $this->assertEquals(25.0, $exchanges[0]->getAmount());
        $this->assertEquals(2.0, $exchanges[0]->getRemainingAmount());

        // Exchange of P3 => C: 25 - 25 ~> 0
        $this->assertCorrectExchangeRelation($exchanges[1], 1, 4);
        $this->assertEquals(25.0, $exchanges[1]->getAmount());
        $this->assertEquals(0.0, $exchanges[1]->getRemainingAmount());

        // Exchange of C => P2: 27 - 10 ~> 17
        $this->assertCorrectExchangeRelation($exchanges[2], 3, 1);
        $this->assertEquals(10.0, $exchanges[2]->getAmount());
        $this->assertEquals(17.0, $exchanges[2]->getRemainingAmount());

        // Exchange of P3 => P2: 10 - 10 ~> 0
        $this->assertCorrectExchangeRelation($exchanges[3], 3, 1);
        $this->assertEquals(10.0, $exchanges[3]->getAmount());
        $this->assertEquals(0.0, $exchanges[3]->getRemainingAmount());

        // Exchange of P2 => C: 25 - 10 ~> 15
        $this->assertCorrectExchangeRelation($exchanges[4], 3, 4);
        $this->assertEquals(10.0, $exchanges[4]->getAmount());
        $this->assertEquals(15.0, $exchanges[4]->getRemainingAmount());

        // Exchange of C => P2: 10 - 10 ~> 0
        $this->assertCorrectExchangeRelation($exchanges[5], 3, 4);
        $this->assertEquals(10.0, $exchanges[5]->getAmount());
        $this->assertEquals(0.0, $exchanges[5]->getRemainingAmount());
    }

    private function assertCorrectTransactionRelation(Transaction $transaction, int $debtorId, int $loanerId): void
    {
        $this->assertEquals($debtorId, $transaction->getDebtor()->getId());
        $this->assertEquals($loanerId, $transaction->getLoaner()->getId());
    }

    private function assertCorrectExchangeRelation(Exchange $exchange, int $from, int $to): void
    {
        $this->assertEquals($from, $exchange->getDebt()->getOwner()->getId());
        $this->assertEquals($to, $exchange->getLoan()->getTransaction()->getDebtor()->getId());
    }


    public function testAddInitialUserCollectionsToGroupEventUserGroupCheckAvoidDoubling(): void
    {
        /** @var GroupEventUserCollectionRepository $userCollectionRepo */
        $userCollectionRepo = $this->getService(GroupEventUserCollectionRepository::class);

        /** @var GroupEventRepository $GroupEventRepository */
        $GroupEventRepository = $this->getService(GroupEventRepository::class);


        // create the first event
        /** @var User $event1_creator */
        $event1_creator = $this->getFixtureEntityByIdent('event1_creator');

        /** @var User $event1_participant1 */
        $event1_participant1 = $this->getFixtureEntityByIdent('event1_participant1');

        /** @var User $event1_participant2 */
        $event1_participant2 = $this->getFixtureEntityByIdent('event1_participant2');

        /** @var User $event1_participant3 */
        $event1_participant3 = $this->getFixtureEntityByIdent('event1_participant3');

        /** @var User $event3_participant1 */
        $event3_participant1 = $this->getFixtureEntityByIdent('event3_participant1');

        $dataEvent1 = new GroupEventInitData();
        $dataEvent1->setDescription('Test Event');
        $dataEvent1->setCreator($event1_creator);
        $dataEvent1->setOpen(true);
        $dataEvent1->setSelectedUsers(
            [$event1_creator, $event1_participant1, $event1_participant2, $event1_participant3]
        );

        $before = $GroupEventRepository->count([]);
        $event1 = $this->groupEventManager->initEvent($dataEvent1);
        $this->assertEquals($before+1, $GroupEventRepository->count([]));

        $this->assertEquals($event1_creator, $event1->getCreator());
        $this->assertEquals('Test Event', $event1->getDescription());

        //store a second with the same users
        $dataEvent2 = new GroupEventInitData();
        $dataEvent2->setDescription('Test Event 2');
        $dataEvent2->setCreator($event1_creator);
        $dataEvent2->setOpen(true);
        $dataEvent2->setSelectedUsers(
            [$event1_creator, $event1_participant1, $event1_participant2, $event1_participant3, $event3_participant1]
        );

        $event3 = $this->groupEventManager->initEvent($dataEvent2);
        $this->assertEquals($before+2, $GroupEventRepository->count([]));

        $this->assertEquals($event1_creator, $event3->getCreator());
        $this->assertEquals('Test Event 2', $event3->getDescription());

        // now lets init the userGroups for the event 3 from fixtures
        $this->assertEquals(3, $userCollectionRepo->count([]));
        $this->groupEventManager->addInitialUserCollectionsToGroupEvent($dataEvent1, $event1);



        $this->assertEquals(8, $userCollectionRepo->count([]));

        $this->groupEventManager->addInitialUserCollectionsToGroupEvent($dataEvent2, $event3);
        $all = $userCollectionRepo->findAll();
//        foreach ($all as $c){
//            dump($c->getName());
//        }

        $this->assertCount(5, $event3->getUsers());
        $this->assertEquals(14, $userCollectionRepo->count([]));
    }

    public function testGetInitialGroupAlgorithm()
    {
        $a = [];
        for ($i = 0; $i < 5; $i++) {
            $a[] = new class (rand(1, 10000)) {
                public function __construct(private int $value) { }

                public function count(): int
                {
                    return $this->value;
                }
            };
        }

        $a[] = new class (10001) {
            public function __construct(private int $value) { }

            public function count(): int
            {
                return $this->value;
            }
        };

        for ($i = 0; $i < 5; $i++) {
            $a[] = new class (rand(1, 10000)) {
                public function __construct(private int $value) { }

                public function count(): int
                {
                    return $this->value;
                }
            };
        }

        $r = array_reduce($a, function ($x, $y) {
            if ($x->count() > $y->count()) {
                return $x;
            }
            return $y;
        }, $a[0]);

        $this->assertEquals(10001, $r->count());
    }
}
