<?php

namespace App\Repository;

use App\Entity\Post;
use App\Enum\PostStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Derniers posts publiés
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Posts les plus populaires (score basé sur les votes)
     */
    public function findTopScored(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.votes', 'v')
            ->andWhere('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)

            ->addSelect("
                SUM(
                    CASE 
                        WHEN v.type = 'like' THEN 1
                        WHEN v.type = 'laugh' THEN 2
                        WHEN v.type = 'angry' THEN -1
                        ELSE 0
                    END
                ) AS HIDDEN score
            ")

            ->groupBy('p.id')
            ->orderBy('score', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Pagination des posts publiés
     */
    public function findPaginated(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre total de posts publiés
     */
    public function countPublished(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}