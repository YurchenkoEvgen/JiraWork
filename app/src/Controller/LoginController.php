<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        $user = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();
        dump($security->getUser());
        return $this->render('login/index.html.twig', [
            'last_user' => $user,
            'error' => $error
        ]);
    }
}
