<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\MicroPost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    /**
     * @param MicroPost $post
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    #[Route('/like/{id}', name: 'app_like')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function like(
        MicroPost $post,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $currentUser = $this->getUser();
        $post->addLikedBy($currentUser);
        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @param MicroPost $post
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    #[Route('/unlike/{id}', name: 'app_unlike')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unlike(
        MicroPost $post,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $currentUser = $this->getUser();
        $post->removeLikedBy($currentUser);
        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
