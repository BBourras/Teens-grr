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
     * Crée un commentaire
     */
    public function create(Comment $comment, Post $post, User $author): void
    {
        $comment->setPost($post)
                ->setAuthor($author)
                ->setStatus(CommentStatus::PUBLISHED);

        $this->em->persist($comment);
        $this->em->flush();
    }

    /**
     * Suppression logique (soft delete)
     */
    public function delete(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::DELETED)
                ->setDeletedAt(new \DateTimeImmutable());

        $this->em->flush();
    }

    /**
     * Masquage automatique (ex: 5 signalements)
     */
    public function autoHide(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::AUTO_HIDDEN);
        $this->em->flush();
    }

    /**
     * Masquage manuel par modérateur
     */
    public function hideByModerator(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::HIDDEN_BY_MODERATOR);
        $this->em->flush();
    }

    /**
     * Restauration d’un commentaire
     */
    public function restore(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::PUBLISHED);
        $this->em->flush();
    }

    /**
     * Récupère uniquement les commentaires visibles d’un post
     */
    public function getVisibleByPost(Post $post): array
    {
        return $this->commentRepository->createQueryBuilder('c')
            ->andWhere('c.post = :post')
            ->andWhere('c.status = :status')
            ->setParameter('post', $post)
            ->setParameter('status', CommentStatus::PUBLISHED)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}