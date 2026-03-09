<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reports')]
class ReportController extends AbstractController
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Signaler un post.
     * - Un utilisateur ne peut signaler qu’une fois (géré côté service)
     */
    #[Route('/posts/{id}/report', name: 'report_post', methods: ['POST'])]
    public function reportPost(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $reason = $request->request->get('reason');

        $this->reportService->reportPost($post, $user, $reason);

        $this->addFlash('success', 'Post signalé.');

        return $this->redirectToRoute('post_show', [
            'id' => $post->getId()
        ]);
    }

    /**
     * Signaler un commentaire.
     */
    #[Route('/comments/{id}/report', name: 'report_comment', methods: ['POST'])]
    public function reportComment(Comment $comment, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $reason = $request->request->get('reason');

        $this->reportService->reportComment($comment, $user, $reason);

        $this->addFlash('success', 'Commentaire signalé.');

        return $this->redirectToRoute('post_show', [
            'id' => $comment->getPost()->getId()
        ]);
    }
}