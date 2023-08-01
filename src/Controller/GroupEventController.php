<?php

namespace App\Controller;

use App\Entity\GroupEvent;
use App\Entity\User;
use App\Form\GroupEvent\CreateEventPaymentType;
use App\Repository\UserRepository;
use App\Service\GroupEvent\Event\Form\GroupEventInitData;
use App\Service\GroupEvent\Event\Form\InitGroupEventType;
use App\Service\GroupEvent\GroupEventManager;
use App\Service\GroupEvent\Payment\Form\GroupEventPaymentData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/event')]
class GroupEventController extends AbstractController
{

    public function __construct(private readonly GroupEventManager $groupEventManager)
    {
    }

    #[Route(path: '/create', name: 'event_init')]
    public function createEvent(Request $request, UserRepository $repository): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $allUsers = $repository->findAll();

        $groupEventInitData = (new GroupEventInitData());
        $groupEventInitData->setAllUsers($allUsers);
        $groupEventInitData->setSelectedUsers([]);
        $groupEventInitData->setCreator($requester);
        $groupEventInitData->setOpen(true);
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

    #[Route(path: '/{groupEvent}/show', name: 'event_show')]
    public function showEvent(GroupEvent $groupEvent, Request $request): Response
    {
        $forbidden = $this->checkIfUserIsParticipantOfEvent($groupEvent, $request);
        if (!is_null($forbidden)){
            return $forbidden;
        }

        return $this->render('event/event.show.html.twig', [
            'event' => $groupEvent,
        ]);
    }

    #[Route(path: '/{groupEvent}/add', name: 'event_payment_add')]
    public function editEvent(GroupEvent $groupEvent, Request $request): Response
    {
        $forbidden = $this->checkIfUserIsParticipantOfEvent($groupEvent, $request);
        if (!is_null($forbidden)){
            return $forbidden;
        }

        /** @var User $requester */
        $requester = $this->getUser();

        $groups = $groupEvent->getUserGroups()->toArray();

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

            $this->addFlash('info', 'Zahlung hinzugefügt');
            return $this->redirect($this->generateUrl('event_show', ['groupEvent' => $groupEvent->getId()]));
        }

        return $this->render('event/event.payment.create.html.twig', [
            'form' => $form->createView(),
            'groups' =>  $groups,
            'event' => $groupEvent
        ]);
    }

    #[Route(path: '/{groupEvent}/calculate', name: 'event_calculate')]
    public function calculateEvent(GroupEvent $groupEvent, Request $request): Response
    {
        $forbidden = $this->checkIfUserIsParticipantOfEvent($groupEvent, $request);
        if (!is_null($forbidden)){
            return $forbidden;
        }

        /** @var User $requester */
        $requester = $this->getUser();

        $restricted = false;
        if ($groupEvent->getCreator() !== $requester){
            $this->addFlash('warning', 'Nur der Ersteller eines Events darf die Auswertung triggern');
            $restricted = true;
        }

        if (!is_null($groupEvent->getEvaluated())){
            $this->addFlash('warning', 'Das Event ist bereits ausgewertet');
            $restricted = true;
        }


        if (!$restricted){
            $this->groupEventManager->calculateGroupEventFinalBill($groupEvent);
        }

        return $this->redirect(
            $this->generateUrl('event_show', [
                'groupEvent' => $groupEvent->getId(),
            ])
        );
    }

    #[Route(path: '/{groupEvent}/calculation', name: 'event_show_calculation')]
    public function showCalculation(GroupEvent $groupEvent, Request $request): Response
    {
        $forbidden = $this->checkIfUserIsParticipantOfEvent($groupEvent, $request);
        if (!is_null($forbidden)){
            return $forbidden;
        }

        $slide = $request->query->getInt('slide', 1);

        $results = $this->groupEventManager->getResultsDtosForEvent($groupEvent);
        if ($slide > count($results) || !$request->query->has('slide')){
            return $this->redirect($this->generateUrl('event_show_calculation', ['groupEvent' => $groupEvent->getId(), 'slide' => 0]));
        }

        $left = '';
        $right = '';
        if ($slide > 0) {
            $left = $results[$slide-1]->getLoaner()->getFirstName();
        }
        if ($slide < count($results)-1){
            $right = $results[$slide+1]->getLoaner()->getFirstName();
        }


        return $this->render('event/event.calculation.show.html.twig', [
            'event' => $groupEvent,
            'user' => $results[$slide],
            'last' => $left,
            'next' => $right
        ]);
    }

    private function checkIfUserIsParticipantOfEvent(GroupEvent $event, Request $request): ?RedirectResponse
    {
        if (!in_array($this->getUser(), $event->getUsers())) {
            $referer = $request->headers->get('referer');
            $this->addFlash('warning', 'Du bist kein Teilnehmer dieses Events');
            if (is_null($referer)){
                return $this->redirect($this->generateUrl('app_home', []));
            }
            return new RedirectResponse($referer);
        }

        return null;
    }
}
