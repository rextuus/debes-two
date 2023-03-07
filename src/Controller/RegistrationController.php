<?php

namespace App\Controller;

use App\Form\UserType;
use App\Service\User\UserData;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'registration')]
    public function registerNewUser(Request $request, UserService $userService): Response
    {
        $registrationForm = $this->createForm(UserType::class, new UserData());

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            /** @var UserData $data */
            $data = $registrationForm->getData();

            $userService->storeUser($data);

            return $this->redirect($this->generateUrl('app_login'));
        }

        return $this->render('registration/registration.html.twig', [
            'registrationForm' => $registrationForm->createView(),
        ]);
    }
}
