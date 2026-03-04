<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PostService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PostRepository $postRepository,
    ) {}

    /*
     |--------------------------------------------------------------------------
     | CREATE
     |--------------------------------------------------------------------------
     */

    public function create(Post $post, User $author): void
    {
        $post->setAuthor($author);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setStatus(PostStatus::PUBLISHED);

        $this->em->persist($post);
        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | UPDATE
     |--------------------------------------------------------------------------
     */

    public function update(Post $post): void
    {
        $post->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | DELETE (SOFT DELETE)
     |--------------------------------------------------------------------------
     */

    public function delete(Post $post): void
    {
        $post->setStatus(PostStatus::DELETED);
        $post->setDeletedAt(new \DateTimeImmutable());

        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | QUERY BUILDERS
     |--------------------------------------------------------------------------
     */

    public function getLatestQueryBuilder(): QueryBuilder
    {
        return $this->postRepository->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->orderBy('p.createdAt', 'DESC');
    }

    public function getTopScoredQueryBuilder(): QueryBuilder
    {
        return $this->postRepository->createQueryBuilder('p')
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
            ->orderBy('score', 'DESC');
    }

    /*
     |--------------------------------------------------------------------------
     | PAGINATION GENERIC
     |--------------------------------------------------------------------------
     */

    public function getPaginated(QueryBuilder $qb, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        $query = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $items = $query->getResult();

        // total count
        $countQb = clone $qb;
        $countQb->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->select('COUNT(DISTINCT p.id)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => (int) ceil($total / $limit),
        ];
    }
}