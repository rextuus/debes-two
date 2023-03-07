<?php

namespace App\Controller;

use App\Service\Transaction\TransactionService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class LandingController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/', name: 'landing')]
    public function index(UserService $userService, TransactionService $transactionService): Response
    {
        $user = $this->security->getUser();
        $transactions = $transactionService->getAllTransactionBelongingUser($user);
        dump($transactions[0]);
        return $this->render('landing/index.html.twig', [
            'controller_name' => 'LandingController',
            'transactions' => $transactions,
        ]);
    }
}
