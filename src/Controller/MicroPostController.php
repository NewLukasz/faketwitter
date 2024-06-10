<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostController extends AbstractController
{
    #[Route('/micro-post', name: 'app_micro_post')]
    public function index(
        MicroPostRepository $posts
    ): Response
    {
        return $this->render('micro_post/index.html.twig', [
            'posts' => $posts->findAllWithComments(),
        ]);
    }

    #[Route('/micro-post/top-liked', name: 'app_micro_post_topliked')]
    public function topLiked(
        MicroPostRepository $posts
    ): Response
    {
        return $this->render('micro_post/top_liked.html.twig', [
            'posts' => $posts->findAllWithMinLikes(2),
        ]);
    }

    #[Route('/micro-post/follows', name: 'app_micro_post_follows')]
    public function postsFromFollows(
        MicroPostRepository $posts
    ): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return $this->render('micro_post/follows.html.twig', [
            'posts' => $posts->findAllByAuthors($currentUser->getFollowers()),
        ]);
        //TODO ogarnąć o co tu chodzi powinno być Follows
    }

    #[Route('/micro-post/{post}', name: 'app_micro_post_show')]
    #[IsGranted(MicroPost::VIEW, 'post')]
    public function showOne(
        MicroPost $post
    ): Response
    {
        return $this->render('micro_post/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/micro-post/add', 'app_micro_post_add', priority: 2)]
    #[isGranted('ROLE_WRITER')]
    public function add(
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        $microPost = new MicroPost();
        $form = $this->createForm(MicroPostType::class, $microPost);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MicroPost $post */
            $post = $form->getData();
            $post->setCreated(new DateTime());
            $post->setAuthor($this->getUser());

            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Your micropost is added');
            return $this->redirectToRoute('app_micro_post');
        }

        return $this->render('micro_post/add.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    #[Route('/micro-post/{post}/edit', 'app_micro_post_edit')]
    #[isGranted(MicroPost::EDIT, 'post')]
    public function edit(
        MicroPost              $post,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted(MicroPost::EDIT, $post);

        $form = $this->createForm(MicroPostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Your micropost has been updated');
            return $this->redirectToRoute('app_micro_post');
        }

        return $this->render('micro_post/edit.html.twig',
            [
                'form' => $form,
                'post' => $post
            ]
        );
    }


    #[Route('/micro-post/{post}/comment', 'app_micro_post_comment')]
    #[isGranted('ROLE_COMMENTER')]
    public function addComment(
        MicroPost              $post,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(CommentType::class, new Comment());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();
            $comment->setMicroPost($post);
            $comment->setAuthor($this->getUser());

            //tutaj sprawdzić czy post się zapisuje

            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Your comment has been updated');
            return $this->redirectToRoute(
                'app_micro_post_show',
                [
                    'post' => $post->getId()
                ]
            );
        }

        return $this->render('micro_post/comment.html.twig',
            [
                'form' => $form,
                'post' => $post
            ]
        );
    }
}
