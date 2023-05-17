<?php

namespace App\Controller;

use App\Cdn\CloudinaryService;
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
    public function index(TransactionService $transactionService, CloudinaryService $cloudinaryService): Response
    {
        $sliderImageNames = ['home.png', 'borrow.png', 'exchange.png', 'write_2.png', 'hunt.png'];

        $cdnPath = 'debes/app/';
        $sliderImages = [];
        foreach ($sliderImageNames as $sliderImageName) {
            $sliderImages[] = $cloudinaryService->getImageFromCdn($cdnPath . $sliderImageName, 500, 500);
        }

        return $this->render('landing/home.html.twig', [
            'controller_name' => 'LandingController',
            'sliderImages' => $sliderImages,
        ]);
    }
}
