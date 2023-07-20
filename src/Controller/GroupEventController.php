<?php

namespace App\Controller;

use App\Entity\GroupEvent;
use App\Entity\User;
use App\Form\GroupEvent\CreateEventPaymentType;
use App\Form\GroupEvent\InitGroupEventType;
use App\Repository\UserRepository;
use App\Service\GroupEvent\GroupEventInitData;
use App\Service\GroupEvent\GroupEventManager;
use App\Service\GroupEvent\Payment\GroupEventPaymentData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/event')]
class GroupEventController extends AbstractController
{

    public function __construct(private GroupEventManager $groupEventManager)
    {
    }

    #[Route('/create', name: 'event_init')]
    public function createEvent(Request $request, UserRepository $repository): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $allUsers = $repository->findAll();

        $groupEventInitData = (new GroupEventInitData());
        $groupEventInitData->setAllUsers($allUsers);
        $groupEventInitData->setSelectedUsers([]);
        $groupEventInitData->setCreator($requester);
        $form = $this->createForm(
            InitGroupEventType::class,
            $groupEventInitData,
            ['requester' => $requester, 'users' => $allUsers]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var GroupEventInitData $data */
            $data = $form->getData();

            $event = $this->groupEventManager->initEvent($data);
            $this->groupEventManager->addInitialUserCollectionsToGroupEvent($data, $event);

            return $this->redirect($this->generateUrl('event_show', ['groupEvent' => $event->getId()]));
        }

        return $this->render('event/event.create.html.twig', [
            'form' => $form->createView(),
            'allUsers' => $allUsers,
        ]);
    }

    #[Route('/{groupEvent}/show', name: 'event_show')]
    public function showEvent(GroupEvent $groupEvent, Request $request, UserRepository $repository): Response
    {
        $groupEvent->getUsers();

        return $this->render('event/event.show.html.twig', [
            'event' => $groupEvent,
        ]);
    }

    #[Route('/{groupEvent}/add', name: 'event_payment_add')]
    public function editEvent(GroupEvent $groupEvent, Request $request, UserRepository $repository): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        // TODO need method to check if user is participant of the event

        $groups = $groupEvent->getParticipantGroups()->toArray();

        $paymentData = (new GroupEventPaymentData());
        $paymentData->setGroupEvent($groupEvent);
        $paymentData->setDebtors($groups[0]);
        $paymentData->setLoaner($requester);


        $form = $this->createForm(
            CreateEventPaymentType::class,
            $paymentData,
            ['requester' => $requester]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var GroupEventPaymentData $data */
            $data = $form->getData();

            $this->groupEventManager->addPaymentToEvent($data);


//            $event = $this->groupEventManager->initEvent($data);
//            $this->groupEventManager->addInitialUserCollectionsToGroupEvent($data, $event);

            return $this->redirect($this->generateUrl('event_show', ['groupEvent' => $groupEvent->getId()]));
        }

        return $this->render('event/event.payment.create.html.twig', [
            'form' => $form->createView(),
            'groups' =>  $groups,
            'event' => $groupEvent
        ]);
    }
}
