<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\PaymentOption\PaypalAccountService;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transaction\TransactionService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/me')]
class AccountController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/', name: 'account_overview')]
    public function listTransactionsForUser(TransactionService $transactionService, PaypalAccountService $paypalAccountService): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User){
            throw new Exception();
        }
        $transactions = $transactionService->getAllTransactionBelongingUser($user);
        $totalDebts = $transactionService->getTotalDebtsForUser($user);
        $totalLoans = $transactionService->getTotalLoansForUser($user);
        $totalBalance = $totalLoans - $totalDebts;
        $openDebts = $transactionService->getCountForDebtTransactionsForUserAndState($user, Transaction::STATE_READY);
        $acceptedDebts = $transactionService->getCountForDebtTransactionsForUserAndState($user, Transaction::STATE_ACCEPTED);
        $openLoans = $transactionService->getCountForAllLoanTransactionsForUserAndSate($user, Transaction::STATE_READY);
        $acceptedLoans = $transactionService->getCountForAllLoanTransactionsForUserAndSate($user, Transaction::STATE_ACCEPTED);
        $paypalAccountNeeded = count($paypalAccountService->getPaypalAccountsOfUser($user)) === 0;

        return $this->render('landing/account_overview.html.twig', [
            'controller_name' => 'LandingController',
            'totalBalance' => $totalBalance,
            'transactions' => $transactions,
            'totalDebt' => $totalDebts,
            'totalLoan' => $totalLoans,
            'openDebts' => $openDebts,
            'openLoans' => $openLoans,
            'acceptedDebts' => $acceptedDebts,
            'acceptedLoans' => $acceptedLoans,
            'paypalAccountNeeded' => $paypalAccountNeeded,
        ]);
    }

    #[Route('/debts', name: 'account_debts')]
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

    #[Route('/loans', name: 'account_loans')]
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

    #[Route('/events', name: 'account_event')]
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
