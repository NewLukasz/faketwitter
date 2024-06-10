<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

class ProfileController extends AbstractController
{
    /**
     * @param User $user
     * @param MicroPostRepository $postRepository
     * @return Response
     */
    #[Route('/profile/{id}', name: 'app_profile')]
    public function show(
        User                $user,
        MicroPostRepository $postRepository
    ): Response
    {
        return $this->render(
            'profile/show.html.twig',
            [
                'user' => $user,
                'posts' => $postRepository->findAllByAuthor($user)
            ]
        );
    }

    /**
     * @param User $user
     * @return Response
     */
    #[Route('/profile/{id}/follows', name: 'app_profile_follows')]
    public function follows(
        User $user
    ): Response
    {
        return $this->render(
            'profile/follows.html.twig',
            ['user' => $user]
        );
    }

    /**
     * @param User $user
     * @return Response
     */
    #[Route('/profile/{id}/followers', name: 'app_profile_followers')]
    public function followers(
        User $user
    ): Response
    {
        return $this->render(
            'profile/followers.html.twig',
            ['user' => $user]
        );
    }

}
