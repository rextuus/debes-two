<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Exception\UserNotCorrectParticipantOfTransaction;
use App\Form\ChoiceType;
use App\Form\GroupEvent\InitGroupEventType;
use App\Form\TransactionCreateMultipleType;
use App\Form\TransactionCreateSimpleType;
use App\Repository\UserRepository;
use App\Service\Debt\DebtCreateData;
use App\Service\Debt\DebtDto;
use App\Service\GroupEvent\GroupEventInitData;
use App\Service\GroupEvent\GroupEventManager;
use App\Service\Loan\LoanDto;
use App\Service\Mailer\MailService;
use App\Service\Transaction\DtoProvider;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionCreateMultipleData;
use App\Service\Transaction\TransactionData;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transaction\TransactionService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/event')]
class GroupEventController extends AbstractController
{

    public function __construct(private GroupEventManager $groupEventManager)
    {
    }

    #[Route('/create', name: 'event_create')]
    public function createSimpleTransaction(Request $request, UserRepository $repository): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $allUsers = $repository->findAll();

        $groupEventInitData = (new GroupEventInitData());
        $groupEventInitData->setAllUsers($allUsers);
        $groupEventInitData->setSelectedUsers([]);
        $form = $this->createForm(InitGroupEventType::class, $groupEventInitData, ['requester' => $requester, 'users' => $allUsers]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransactionData $data */
            $data = $form->getData();

            $transaction = $this->transactionService->storeSingleTransaction($data, $requester);

            $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_CREATED);

            return $this->redirect($this->generateUrl('account_loans', ['variant' => 'new']));
        }

        return $this->render('event/event.create.html.twig', [
            'form' => $form->createView(),
            'allUsers' => $allUsers,
        ]);
    }
}
