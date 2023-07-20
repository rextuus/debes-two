<?php

namespace App\Tests\GroupEvent;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Entity\Transaction;
use App\Entity\User;
use App\EntityListener\Event\TransactionExchangeEvent;
use App\Repository\ExchangeRepository;
use App\Repository\TransactionRepository;
use App\Service\GroupEvent\GroupEventInitData;
use App\Service\GroupEvent\GroupEventManager;
use App\Service\GroupEvent\Payment\GroupEventPaymentData;
use App\Service\GroupEvent\Result\GroupEventResultService;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionData;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionRepository;
use App\Service\PaymentAction\PaymentActionService;
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

    public function testGroupEvaluation(): void
    {
        /*
         * Payments
         * creator => all 28 / 4 = 7
         * participant3 => all 100 / 4 = 25
         * creator => all other 60 / 3 = 20
         * participant2 => participant2 90 / 1 = 90
         *
         * creator: 7 => himself, 25 => participant3,
         * participant1: 27 (7+20) => creator, 25 => participant3,
         * participant2: 90 => himself, 27 (7+20) => creator, 25 => participant3,
         * participant3: 25 => himself, 27 (7+20) => creator
         *
         * resulting exchanges
         * creator <=> participant3 | 0 <=> 2
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

        $paymentData = new GroupEventPaymentData();
        $paymentData->setGroupEvent($event);
        $paymentData->setLoaner($event1_participant2);
        $paymentData->setAmount(40);
        $paymentData->setDebtors($all);
        $paymentData->setReason('New payment');

        $payment = $this->groupEventManager->addPaymentToEvent($paymentData);

        $this->groupEventManager->calculateGroupEventFinalBill($event);

        $this->groupEventManager->triggerTransactionCreation($event);

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->getService(TransactionRepository::class);
        $transactions = $transactionRepository->findAll();

        // creator => participant3 (be aware it was automatically exchanged)
        $this->assertEquals(0.0, $transactions[0]->getAmount());
        $this->assertEquals(Transaction::STATE_CONFIRMED, $transactions[0]->getState());

        // participant1 => creator
        $this->assertEquals(27.0, $transactions[1]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[1]->getState());

        // participant1 => participant3
        $this->assertEquals(25.0, $transactions[2]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[2]->getState());

        // participant2 => creator
        $this->assertEquals(17.0, $transactions[3]->getAmount());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transactions[3]->getState());

        // participant2 => participant3
        $this->assertEquals(25.0, $transactions[4]->getAmount());
        $this->assertEquals(Transaction::STATE_READY, $transactions[4]->getState());

        // participant3 => creator (be aware it was automatically exchanged)
        $this->assertEquals(2.0, $transactions[5]->getAmount());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $transactions[5]->getState());

        /** @var ExchangeRepository $exchangeRepository */
        $exchangeRepository = $this->getService(ExchangeRepository::class);
        $exchanges = $exchangeRepository->findAll();

        $this->assertEquals(25.0, $exchanges[0]->getAmount());
        $this->assertEquals(2.0, $exchanges[0]->getRemainingAmount());
    }

    public function testAddInitialUserCollectionsToGroupEventUserGroupCheckAvoidDoubling(): void
    {
        /** @var GroupEventUserCollectionRepository $userCollectionRepo */
        $userCollectionRepo = $this->getService(GroupEventUserCollectionRepository::class);

        // create the first event
        /** @var User $event1_creator */
        $event1_creator = $this->getFixtureEntityByIdent('event1_creator');

        /** @var User $event1_participant1 */
        $event1_participant1 = $this->getFixtureEntityByIdent('event1_participant1');

        /** @var User $event1_participant2 */
        $event1_participant2 = $this->getFixtureEntityByIdent('event1_participant2');

        /** @var User $event1_participant3 */
        $event1_participant3 = $this->getFixtureEntityByIdent('event1_participant3');

        $dataEvent1 = new GroupEventInitData();
        $dataEvent1->setDescription('Test Event');
        $dataEvent1->setCreator($event1_creator);
        $dataEvent1->setSelectedUsers([$event1_creator, $event1_participant1, $event1_participant2, $event1_participant3]);

        $event1 = $this->groupEventManager->initEvent($dataEvent1);

        $this->assertEquals($event1_creator, $event1->getCreator());
        $this->assertEquals('Test Event', $event1->getDecscription());

        //store a second with the same users
        $dataEvent2 = new GroupEventInitData();
        $dataEvent2->setDescription('Test Event 2');
        $dataEvent2->setCreator($event1_creator);
        $dataEvent2->setSelectedUsers([$event1_creator, $event1_participant1, $event1_participant2, $event1_participant3]);

        $event2 = $this->groupEventManager->initEvent($dataEvent2);
        $this->assertEquals($event1_creator, $event2->getCreator());
        $this->assertEquals('Test Event 2', $event2->getDecscription());

        // now lets init the userGroups for the event
        $this->assertEquals(0, $userCollectionRepo->count([]));
        $collection = $this->groupEventManager->addInitialUserCollectionsToGroupEvent($dataEvent1, $event1);
        $this->assertEquals(2, $userCollectionRepo->count([]));
        $collection = $this->groupEventManager->addInitialUserCollectionsToGroupEvent($dataEvent2, $event2);
        $this->assertEquals(2, $userCollectionRepo->count([]));
    }

    public function testAddInitialUserCollectionsToGroupEvent(): void
    {
        // create the first event
        /** @var User $event1_creator */
        $event1_creator = $this->getFixtureEntityByIdent('event1_creator');

        /** @var User $event1_participant1 */
        $event1_participant1 = $this->getFixtureEntityByIdent('event1_participant1');

        /** @var User $event1_participant2 */
        $event1_participant2 = $this->getFixtureEntityByIdent('event1_participant2');

        /** @var User $event1_participant3 */
        $event1_participant3 = $this->getFixtureEntityByIdent('event1_participant3');

        $dataEvent1 = new GroupEventInitData();
        $dataEvent1->setDescription('Test Event');
        $dataEvent1->setCreator($event1_creator);
        $dataEvent1->setSelectedUsers([$event1_creator, $event1_participant1, $event1_participant2, $event1_participant3]);

        $event1 = $this->groupEventManager->initEvent($dataEvent1);

        $this->assertEquals($event1_creator, $event1->getCreator());
        $this->assertEquals('Test Event', $event1->getDecscription());

        $collectionData = new GroupEventUserCollectionData();
        $collectionData->setInitial(true);
        $collectionData->setUsers([$event1_participant1]);

        $collection = $this->groupEventManager->addInitialUserCollectionsToGroupEvent($dataEvent1, $event1);
        $this->assertEquals($event1_participant1, $collection->getUsers()[0]);

        //store a second with the same users


        // lets do some payments
//        $paymentData = new GroupEventPaymentData();
//        $paymentData->setGroupEvent($event1);
//        $paymentData->setLoaner($event1_participant2);
//        $paymentData->setAmount(99.01);
//        $paymentData->setDebtors($collection);
//
//        $payment = $this->groupEventManager->addPaymentToEvent($paymentData);
//        $this->assertEquals($event1_participant2, $payment->getLoaner());
//
//        $paymentData = new GroupEventPaymentData();
//        $paymentData->setGroupEvent($event1);
//        $paymentData->setLoaner($event1_participant3);
//        $paymentData->setAmount(100.99);
//        $paymentData->setDebtors($collection);
//
//        $payment = $this->groupEventManager->addPaymentToEvent($paymentData);
//
//        $this->assertEquals(2, $event1->getPayments()->count());
//        $sum = $this->groupEventManager->getTotalAmountOfEvent($event1);
//
//        $this->assertEquals(200.00, $sum);
    }
}
