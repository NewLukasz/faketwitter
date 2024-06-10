<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Repository\CommentRepository;
use App\Repository\MicroPostRepository;
use App\Repository\UserProfileRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HelloController extends AbstractController
{
    private array $messages = [
        ['message' => 'hello', 'created' => '2024/02/14'],
        ['message' => 'hi', 'created' => '2024/02/15'],
        ['message' => 'bye', 'created' => '2023/02/16']
    ];


    #[Route('', name: 'app_index')]
    public function index(
        UserProfileRepository  $userProfileRepository,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,
        MicroPostRepository    $postRepository,
        CommentRepository      $commentRepository
    ): Response
    {
        $user = $userRepository->find(18);
        $user->setRoles(['ROLE_EDITOR']);
        $entityManager->persist($user);
        $entityManager->flush();


        return $this->render(
            'hello/index.html.twig',
            [
                'messages' => $this->messages,
                'limit' => 3
            ]
        );
    }

    #[Route('/messages/{id}', name: 'app_show_one')]
    public function showOne($id): Response
    {
        return $this->render(
            'hello/show_one.html.twig',
            [
                'message' => $this->messages[$id]
            ]
        );
    }
}
