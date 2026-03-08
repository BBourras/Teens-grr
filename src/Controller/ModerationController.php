<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Service\PostService;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/moderation')]
class ModerationController extends AbstractController
{
    public function __construct(
        private PostService $postService,
        private CommentService $commentService
    ) {}

    /**
     * Dashboard principal de modération
     */
    #[Route('', name: 'moderation_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        return $this->render('moderation/dashboard.html.twig', [
            'posts' => $this->postService->getAutoHidden(),
            'comments' => $this->commentService->getAutoHidden(),
        ]);
    }

    /**
     * Restaurer un post
     */
    #[Route('/posts/{id}/restore', name: 'moderation_post_restore', methods: ['POST'])]
    public function restorePost(Post $post): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $this->postService->restore($post);

        $this->addFlash('success', 'Post restauré.');
        return $this->redirectToRoute('moderation_dashboard');
    }

    /**
     * Restaurer un commentaire
     */
    #[Route('/comments/{id}/restore', name: 'moderation_comment_restore', methods: ['POST'])]
    public function restoreComment(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $this->commentService->restore($comment);

        $this->addFlash('success', 'Commentaire restauré.');
        return $this->redirectToRoute('moderation_dashboard');
    }

    /**
     * Supprimer définitivement un post
     */
    #[Route('/posts/{id}/delete', name: 'moderation_post_delete', methods: ['POST'])]
    public function hardDeletePost(Post $post): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $this->postService->hardDelete($post);

        $this->addFlash('success', 'Post supprimé définitivement.');
        return $this->redirectToRoute('moderation_dashboard');
    }

    /**
     * Supprimer définitivement un commentaire
     */
    #[Route('/comments/{id}/delete', name: 'moderation_comment_delete', methods: ['POST'])]
    public function hardDeleteComment(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $this->commentService->hardDelete($comment);

        $this->addFlash('success', 'Commentaire supprimé définitivement.');
        return $this->redirectToRoute('moderation_dashboard');
    }
}