<?php

namespace App\Tests\GroupEvent;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\GroupEvent\GroupEventData;
use App\Service\GroupEvent\GroupEventInitData;
use App\Service\GroupEvent\GroupEventManager;
use App\Service\GroupEvent\GroupEventService;
use App\Service\GroupEvent\Payment\GroupEventPaymentData;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionData;
use App\Service\PaymentAction\PaymentActionData;
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


    public function testFindExchangeCandidatesForTransaction(): void
    {
        /** @var User $creator */
        $creator = $this->getFixtureEntityByIdent('creator');

        /** @var User $participant1 */
        $participant1 = $this->getFixtureEntityByIdent('user2');

        /** @var User $loaner */
        $loaner = $this->getFixtureEntityByIdent('loaner');

        /** @var User $loaner2 */
        $loaner2 = $this->getFixtureEntityByIdent('loaner2');

        $data = new GroupEventInitData();
        $data->setDescription('Test Event');
        $data->setCreator($creator);

        $event = $this->groupEventManager->initEvent($data);

        $this->assertEquals($creator, $event->getCreator());
        $this->assertEquals('Test Event', $event->getDecscription());

        $collectionData = new GroupEventUserCollectionData();
        $collectionData->setInitial(true);
        $collectionData->setUsers([$participant1]);

        $collection = $this->groupEventManager->addUserCollectionToGroupEvent($collectionData);
        $this->assertEquals($participant1, $collection->getUsers()[0]);

        $paymentData = new GroupEventPaymentData();
        $paymentData->setGroupEvent($event);
        $paymentData->setLoaner($loaner);
        $paymentData->setAmount(99.01);
        $paymentData->setDebtors($collection);

        $payment = $this->groupEventManager->addPaymentToEvent($paymentData);
        $this->assertEquals($loaner, $payment->getLoaner());

        $paymentData = new GroupEventPaymentData();
        $paymentData->setGroupEvent($event);
        $paymentData->setLoaner($loaner2);
        $paymentData->setAmount(100.99);
        $paymentData->setDebtors($collection);

        $payment = $this->groupEventManager->addPaymentToEvent($paymentData);

        $this->assertEquals(2, $event->getPayments()->count());
        $sum = $this->groupEventManager->getTotalAmountOfEvent($event);

        $this->assertEquals(200.00, $sum);
    }
}
