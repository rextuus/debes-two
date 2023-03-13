<?php

namespace App\Controller;

use App\Entity\User;
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

    #[Route('/', name: 'landing')]
    public function index(UserService $userService, TransactionService $transactionService): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User){
            throw new Exception();
        }
        $transactions = $transactionService->getAllTransactionBelongingUser($user);
        dump($transactions[0]);
        return $this->render('landing/index.html.twig', [
            'controller_name' => 'LandingController',
            'transactions' => $transactions,
        ]);
    }
}
