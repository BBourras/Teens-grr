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

#[Route('/posts/{postId}/comments')]
class CommentController extends AbstractController
{
    public function __construct(
        private CommentService $commentService,
        private Security $security
    ) {}

    /**
     * Création d’un commentaire sur un post.
     * - Accessible uniquement aux utilisateurs connectés
     * - Validation via CommentFormType
     */
    #[Route('', name: 'comment_create', methods: ['POST'])]
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

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
        }

        return $this->redirectToRoute('post_show', [
            'postId' => $post->getId()
        ]);
    }

    /**
     * Suppression logique d’un commentaire.
     * Permission gérée par Voter (COMMENT_DELETE).
     */
    #[Route('/{commentId}/delete', name: 'comment_delete', methods: ['POST', 'DELETE'])]
    public function delete(Post $post, Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('COMMENT_DELETE', $comment);

        // Sécurité supplémentaire : vérifie l'appartenance au post
        if ($comment->getPost() !== $post) {
            throw $this->createNotFoundException('Commentaire non trouvé pour ce post.');
        }

        $this->commentService->delete($comment);

        $this->addFlash('success', 'Commentaire supprimé avec succès.');

        return $this->redirectToRoute('post_show', [
            'postId' => $post->getId()
        ]);
    }
}