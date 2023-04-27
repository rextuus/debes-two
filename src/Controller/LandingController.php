<?php

namespace App\Controller;

use App\Service\Transaction\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(TransactionService $transactionService): Response
    {
        return $this->render('landing/home.html.twig', [
            'controller_name' => 'LandingController',
        ]);
    }
}
