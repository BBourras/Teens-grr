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

#[Route('/posts/{id}/comments')]
class CommentController extends AbstractController
{
    public function __construct(
        private CommentService $commentService,
        private Security $security
    ) {}

    #[Route('', name: 'comment_create', methods: ['POST'])]
    public function create(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentService->create($comment, $post, $this->getUser());
            $this->addFlash('success', 'Commentaire publié !');
        } else {
            $this->addFlash('error', 'Impossible de publier le commentaire.');
        }

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

    #[Route('/{id}/delete', name: 'comment_delete', methods: ['POST', 'DELETE'])]
    public function delete(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('COMMENT_DELETE', $comment);

        $this->commentService->delete($comment);
        $this->addFlash('success', 'Commentaire supprimé !');

        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }
}