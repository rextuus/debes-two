<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Mailer\CustomMailer;
use App\Service\Mailer\MailService;
use App\Service\Transaction\TransactionService;
use App\Service\User\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class LandingController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(UserService $userService, TransactionService $transactionService, MailService $mailService): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User){
            throw new Exception();
        }
        $transactions = $transactionService->getAllTransactionBelongingUser($user);
        $totalDebts = $transactionService->getTotalDebtsForUser($user);
        $totalLoans = $transactionService->getTotalLoansForUser($user);
        $totalBalance = $totalLoans - $totalDebts;
        $openDebts = $transactionService->getCountForDebtTransactionsForUserAndState($user, Transaction::STATE_CREATED);
        $acceptedDebts = $transactionService->getCountForDebtTransactionsForUserAndState($user, Transaction::STATE_ACCEPTED);
        $openLoans = $transactionService->getCountForAllLoanTransactionsForUserAndSate($user, Transaction::STATE_CREATED);
        $acceptedLoans = $transactionService->getCountForAllLoanTransactionsForUserAndSate($user, Transaction::STATE_ACCEPTED);

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
        ]);
    }
}
