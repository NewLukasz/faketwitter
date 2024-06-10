<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/login', name: 'app_login')]
    public function index(
        AuthenticationUtils $authenticationUtils
    ): Response
    {
        $lastUserName = $authenticationUtils->getLastUsername();
        $authErrors = $authenticationUtils->getLastAuthenticationError();
        return $this->render('login/index.html.twig', [
            'lastUsername' => $lastUserName,
            'error' => $authErrors
        ]);
    }

    #[Route('logout', name: 'app_logout')]
    public function logout(): Response {}

}
