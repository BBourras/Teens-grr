<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends AbstractController
{
    public function __construct(
        private CommentService $commentService,
        private Security $security
    ) {}

    #[Route('/posts/{id}/comment', name: 'comment_create', methods: ['POST'])]
    public function create(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->commentService->create(
                $comment,
                $post,
                $this->security->getUser()
            );
        }

        return $this->redirectToRoute('post_show', [
            'id' => $post->getId()
        ]);
    }
}