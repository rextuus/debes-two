<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Exception\UserNotCorrectParticipantOfTransaction;
use App\Form\ChoiceType;
use App\Form\SingleChoiceType;
use App\Form\TransactionCreateMultipleType;
use App\Form\TransactionCreateSimpleType;
use App\Service\Debt\DebtDto;
use App\Service\Debt\Form\DebtCreateData;
use App\Service\Loan\LoanDto;
use App\Service\Mailer\Handler\SendEmailMessage;
use App\Service\Mailer\MailService;
use App\Service\Transaction\DtoProvider;
use App\Service\Transaction\Transaction\Form\TransactionCreateData;
use App\Service\Transaction\Transaction\Form\TransactionData;
use App\Service\Transaction\TransactionCreateMultipleData;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionVariant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/transaction')]
class TransactionController extends AbstractController
{
    public const REQUESTER_VARIANT_LOANER = 'loaner';
    public const REQUESTER_VARIANT_DEBTOR = 'debtor';

    public function __construct(
        private TransactionService $transactionService,
        private MailService $mailService,
        private TransactionProcessor $transactionProcessor,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/create/simple', name: 'transaction_create_simple')]
    public function createSimpleTransaction(Request $request): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactionData = (new TransactionCreateData());
        $form = $this->createForm(TransactionCreateSimpleType::class, $transactionData, ['requester' => $requester]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransactionData $data */
            $data = $form->getData();

            $transaction = $this->transactionService->storeSingleTransaction($data, $requester);

            $message = new SendEmailMessage(MailService::MAIL_DEBT_CREATED, $transaction);
            $this->messageBus->dispatch($message);

            return $this->redirect($this->generateUrl('account_loans', ['variant' => 'new']));
        }

        return $this->render('transaction/transaction.create.simple.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // TODO CHECK IF USED
    #[Route('/', name: 'transaction_list')]
    public function listTransactionsForUser(): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactions = $this->transactionService->getAllTransactionBelongingUser($requester);

        return $this->render('transaction/transaction.list.html.twig', [
            'debtAmount' => 345.77,
            'loanAmount' => 666.77,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/accept/{slug}', name: 'transaction_accept')]
    public function acceptTransaction(Transaction $transaction, Request $request): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            $request->get('variant'),
            Transaction::STATE_READY
        );
        $isDebtor = $request->get('variant') === self::REQUESTER_VARIANT_DEBTOR;

        if ($isDebtor) {
            $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);
            $labels = ['label' => ['submit' => 'akzeptieren', 'decline' => 'ablehnen']];
            $variant = TransactionVariant::DEBT;
        } else {
            $loan = $this->transactionService->getLoanPartOfUserForTransaction($transaction, $requester);
            $labels = ['label' => ['submit' => 'Zurückziehen', 'decline' => 'Zurückziehen']];
            $variant = TransactionVariant::LOAN;
        }
        $dto = $this->transactionService->createDtoFromTransaction($transaction, $variant);

        $form = $this->createForm(ChoiceType::class, null, $labels);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isDebtor) {
                if ($isAccepted) {
                    $this->transactionProcessor->accept($debt);
                    $message = new SendEmailMessage(MailService::MAIL_DEBT_ACCEPTED, $transaction);
                    $this->messageBus->dispatch($message);

                } else {
                    $this->transactionService->declineDebt($debt);
                    $message = new SendEmailMessage(MailService::MAIL_DEBT_DECLINED, $transaction);
                    $this->messageBus->dispatch($message);
                }
                return $this->redirectToRoute('account_debts', []);
            } else {
                if ($isAccepted) {
                    $message = new SendEmailMessage(MailService::MAIL_DEBT_CANCELED, $transaction);
                    $this->messageBus->dispatch($message);
                }
                return $this->redirectToRoute('account_loans', []);
            }
        }

        return $this->render('transaction/transaction.accept.html.twig', [
            'debtVariant' => $isDebtor,
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/process/{slug}', name: 'transaction_process')]
    public function processTransaction(Transaction $transaction, Request $request): Response
    {
//        /** @var User $requester */
//        $requester = $this->getUser();
//
//        $isDebtor = $this->transactionService->checkRequestForVariant(
//            $requester,
//            $transaction,
//            $request->get('variant'),
//            Transaction::STATE_ACCEPTED
//        );
//
//        if ($isDebtor) {
//            $dto = $this->transactionService->createDtoFromTransaction($transaction, true);
//            $labels = ['label' => ['submit' => 'Überweisen', 'decline' => 'Verrechnen']];
//        } else {
//            $dto = $this->transactionService->createDtoFromTransaction($transaction, false);
//            $labels = ['label' => ['submit' => 'Mahn-Mail senden', 'decline' => 'Mahn-Mail senden']];
//        }
//        $dto = $this->transactionService->createDtoFromTransaction($transaction, $isDebtor);
//
//        $form = $this->createForm(ChoiceType::class, null, $labels);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $useTransaction = (bool)$form->get('submit')->isClicked();
//            $useChange = (bool)$form->get('decline')->isClicked();
//
//            if ($isDebtor) {
//                if ($useTransaction) {
//                    return $this->redirect($this->generateUrl('transfer_prepare',
//                        ['slug' => $transaction->getSlug()]));
//                }
//                if ($useChange) {
//                    return $this->redirect($this->generateUrl('exchange_prepare',
//                        ['slug' => $transaction->getSlug()]));
//                }
//                return $this->redirectToRoute('account_debts', []);
//            } else {
//                if ($useTransaction) {
//                    // TODO remove Transaction and send loaner notification
//                }
//                return $this->redirectToRoute('account_loans', []);
//            }
//        }
//
//        return $this->render('transaction/transaction.process.html.twig', [
//            'debtVariant' => $isDebtor,
//            'dto' => $dto,
//            'form' => $form->createView(),
//        ]);
    }

    #[Route('/confirm/{slug}', name: 'transaction_confirm')]
    public function confirmTransaction(Transaction $transaction, Request $request, DtoProvider $dtoProvider): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $isDebtor = $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            $request->get('variant'),
            Transaction::STATE_CLEARED
        );

        $isDebtor = $request->get('variant') === TransactionService::DEBTOR_VIEW;

        if ($isDebtor) {
            $labels = ['label' => ['submit' => 'Bestätigen', 'decline' => 'Bestätigen']];
            $variant = TransactionVariant::DEBT;
        } else {
            $labels = ['label' => ['submit' => 'Bestätigen', 'decline' => 'd']];
            $variant = TransactionVariant::LOAN;
        }
        $dto = $this->transactionService->createDtoFromTransaction($transaction, $variant);


        $form = $this->createForm(SingleChoiceType::class, null, $labels);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();
            if ($isDebtor) {
                if ($isAccepted) {
                    // TODO
                } else {
                    // TODO send loaner notification to remind him that transaction was succeeded
                }
                return $this->redirectToRoute('account_debts', []);
            } else {
                if ($isAccepted) {
                    $this->transactionService->confirmTransaction($transaction);
                    $message = new SendEmailMessage(MailService::MAIL_DEBT_CONFIRMED, $transaction);
                    $this->messageBus->dispatch($message);
                }
                return $this->redirectToRoute('account_loans', []);
            }
        }

        return $this->render('transaction/transaction.confirm.html.twig', [
            'debtVariant' => $isDebtor,
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    // TODO edit is only needed for admin area. Transaction with multiple users will be a new feature in future
    #[Route('/edit', name: 'transaction_edit')]
    public function editTransaction(): Response
    {
        return $this->render('transaction/transaction.create.html.twig', [
            'controller_name' => 'TransactionController',
        ]);
    }

    #[Route('/create', name: 'transaction_create')]
    public function createTransaction(
        Request $request
    ): Response {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactionData = (new TransactionCreateMultipleData());
        $dat = new DebtCreateData();
        $dat->setOwner($requester);
        $transactionData->setDebtorsData([$dat]);
        $form = $this->createForm(TransactionCreateMultipleType::class, $transactionData, ['requester' => $requester]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $requester */
            $requester = $this->getUser();

            /** @var TransactionCreateMultipleData $data */
            $data = $form->getData();

            $this->transactionService->storeMultipleTransaction($data, $requester);
        }

        return $this->render('transaction/transaction.create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/notify/{slug}', name: 'transaction_notify')]
    public function createTransactionNotification(
        Request $request,
        Transaction $transaction,
        MailService $mailService
    ): Response {
        /** @var User $requester */
        $requester = $this->getUser();

        $allowedLoanerStates = [Transaction::STATE_READY, Transaction::STATE_ACCEPTED];
        $allowedDebtorStates = [Transaction::STATE_CLEARED];
        $transactionState = $transaction->getState();
        $requesterVariant = $request->get('variant');

        if ($requesterVariant === self::REQUESTER_VARIANT_DEBTOR) {
            if (in_array($transactionState, $allowedDebtorStates)) {
                // check if user is really the debtor of this transaction
                $currentUserIsDebtor = $this->transactionService->checkRequestForVariant(
                    $requester,
                    $transaction,
                    self::REQUESTER_VARIANT_DEBTOR,
                    $transaction->getState()
                );
                if ($currentUserIsDebtor) {
                    // send Mail => we need a nice way to choose which message should be send

                }
            }
            return $this->redirectToRoute('account_debts', []);
        }

        if ($requesterVariant === self::REQUESTER_VARIANT_LOANER) {
            if (in_array($transactionState, $allowedLoanerStates)) {
                // check if user is really the laoner of this transaction
                try {
                    $currentUserIsLoaner = $this->transactionService->checkRequestForVariant(
                        $requester,
                        $transaction,
                        self::REQUESTER_VARIANT_LOANER,
                        $transaction->getState()
                    );
                } catch (UserNotCorrectParticipantOfTransaction $e) {
                    // TODO SENTRY FORWARDING
                    return $this->redirectToRoute('account_loans', []);
                }

                if ($currentUserIsLoaner) {
                    // send Mail => we need a nice way to choose which message should be send
                    if ($transactionState === Transaction::STATE_READY) {
                        $returnTab = 1;
                    }
                    if ($transactionState === Transaction::STATE_ACCEPTED) {
                        $returnTab = 2;
                    }

                    $message = new SendEmailMessage(MailService::MAIL_DEBT_REMINDER, $transaction);
                    $this->messageBus->dispatch($message);
                    $messageReceiver = $transaction->getLoaner()->getFullName();

                    $this->addFlash(
                        'success',
                        "Wir haben einen Reminder an $messageReceiver geschickt"
                    );
                }
            }
            return $this->redirectToRoute('account_loans', ['variant' => $returnTab]);
        }


//        if (!in_array($transactionState, $allowedLoanerStates)){
//            if ($request->get('variant') === 'debtor'){
//                return $this->redirectToRoute('account_debts', []);
//            }
//            return $this->redirectToRoute('account_loans', []);
//        }
//
//        $currentUserIsDebtor = $this->transactionService->checkRequestForVariant(
//            $requester,
//            $transaction,
//            $request->get('variant'),
//            $transaction->getState()
//        );


        return $this->redirectToRoute('account_loans', []);
    }

    #[Route('/show/{slug}', name: 'transaction_detail')]
    public function showTransaction(
        Request $request,
        Transaction $transaction,
        DtoProvider $dtoProvider
    ): Response {
        $requester = $this->getUser();

        $variant = $this->transactionService->checkRequesterRole($requester, $transaction);

        $dto = $dtoProvider->createTransactionDto($transaction, $variant);
        return $this->render(
            'transaction/transaction.show.detail.html.twig',
            [
                'transaction' => $dto
            ]
        );
    }

}
