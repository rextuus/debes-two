<?php

namespace App\Controller;

use App\Form\UserType;
use App\Service\User\UserData;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends AbstractController
{

    /**
     * @Route("/registration", name="registration")
     */
    public function registerNewUser(Request $request, UserService $userService): Response
    {
        $form = $this->createForm(UserType::class, new UserData());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserData $data */
            $data = $form->getData();

            $userService->storeUser($data);

            return $this->redirect($this->generateUrl('app_login'));
        }

        return $this->render('registration/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
