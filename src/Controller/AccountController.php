<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transaction\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/me', name: 'account_overview')]
    public function listTransactionsForUser(TransactionService $transactionService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactions = $transactionService->getAllTransactionBelongingUser($requester);

        $loans = $transactionService->getTotalLoansForUser($requester);
        $debts = $transactionService->getTotalDebtsForUser($requester);

        return $this->render('transaction/transaction.list.html.twig', [
            'debtAmount' => $debts,
            'loanAmount' => $loans,
            'balance' => $loans - $debts,
            'transactions' => $transactions
        ]);
    }

    #[Route('/me/debts', name: 'account_debts')]
    public function listDebtsForUser(Request $request, TransactionService $service): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();
        $state = $request->get('variant');

        return $this->render('transaction/transaction.list.debts.html.twig', [
            'user' => $requester,
            'state' => $state ?: 'new',
            'isDebt' => true,
        ]);
    }

    #[Route('/me/loans', name: 'account_loans')]
    public function listLoansForUser(Request $request, TransactionService $transactionService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();
        $state = $request->get('variant');

        return $this->render('transaction/transaction.list.loans.html.twig', [
            'user' => $requester,
            'state' => $state ?: 'new',
            'isDebt' => false
        ]);
    }

    #[Route('/me/events', name: 'account_event')]
    public function listEvents(TransactionChangeEventService $changeEventService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $events = $changeEventService->getAllByUser($requester);
        dump($events);

        return $this->render('transaction/transaction.list.loans.html.twig', [
            'user' => $requester
        ]);
    }
}
