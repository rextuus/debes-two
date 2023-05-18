<?php

namespace App\Controller;

use App\Form\UserLoginType;
use App\Form\UserType;
use App\Service\User\UserData;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function loginUser(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = null;
        if (!$error){
            $lastUsername = $authenticationUtils->getLastUsername();
        }
        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
             'error'         => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logoutUser(Security $security): Response
    {
        // logout the user in on the current firewall
        $response = $security->logout();

        // you can also disable the csrf logout
        $response = $security->logout(false);

        // ... return $response (if set) or e.g. redirect to the homepage
        return $this->redirect($this->generateUrl('app_home'));
    }

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

        return $this->render('login/registration.html.twig', [
            'registrationForm' => $registrationForm->createView(),
        ]);
    }
}
