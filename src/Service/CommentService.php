<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\CommentStatus;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommentRepository $commentRepository,
    ) {}

    /**
     * Création d’un commentaire.
     * - Assigne le post
     * - Assigne l’auteur
     * - Définit le statut
     * - Incrémente le compteur du post
     */
    public function create(Comment $comment, Post $post, User $author): void
    {
        $comment->setPost($post)
            ->setAuthor($author)
            ->setStatus(CommentStatus::PUBLISHED);

        // Compteur dénormalisé pour le post
        $post->incrementCommentCount();

        $this->em->persist($comment);
        $this->em->flush();
    }

    /**
     * Suppression logique d’un commentaire. On le marque comme supprimé.
     */
    public function delete(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::DELETED)
            ->setDeletedAt(new \DateTimeImmutable());

        // On décrémente le compteur du post
        $comment->getPost()->decrementCommentCount();

        $this->em->flush();
    }

    /**
     * Suppression définitive d’un commentaire.
     */
    public function hardDelete(Comment $comment): void
    {
        $this->em->remove($comment);
        $this->em->flush();
    }

    /**
     * Masquage automatique suite à signalements.
     */
    public function autoHide(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::AUTO_HIDDEN);
        $this->em->flush();
    }

    /**
     * Masquage manuel par un modérateur.
     */
    public function hideByModerator(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::HIDDEN_BY_MODERATOR);
        $this->em->flush();
    }

    /**
     * Restauration d’un commentaire.
     */
    public function restore(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::PUBLISHED);
        $this->em->flush();
    }

    /**
     * Récupère les commentaires visibles selon le rôle.
     * - Modérateur : voit tout
     * - Utilisateur normal : seulement PUBLISHED
     */
    public function getVisibleByPost(Post $post, ?User $user): array
    {
        if ($user && in_array('ROLE_MODERATOR', $user->getRoles())) {
            return $this->commentRepository->findBy(
                ['post' => $post],
                ['createdAt' => 'DESC']
            );
        }

        return $this->commentRepository->findBy(
            ['post' => $post, 'status' => CommentStatus::PUBLISHED],
            ['createdAt' => 'DESC']
        );
    }

    /**
     * Récupère les commentaires auto-masqués.
     */
    public function getAutoHidden(): array
    {
        return $this->commentRepository->findBy(
            ['status' => CommentStatus::AUTO_HIDDEN],
            ['createdAt' => 'DESC']
        );
    }
}