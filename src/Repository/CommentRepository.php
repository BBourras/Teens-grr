<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use App\Enum\CommentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Retourne les commentaires visibles d’un post
     */
    public function findVisibleByPost(Post $post): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.post = :post')
            ->andWhere('c.status = :status')
            ->setParameter('post', $post)
            ->setParameter('status', CommentStatus::PUBLISHED)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les commentaires visibles d’un post
     */
    public function countVisibleByPost(Post $post): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.post = :post')
            ->andWhere('c.status = :status')
            ->setParameter('post', $post)
            ->setParameter('status', CommentStatus::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}