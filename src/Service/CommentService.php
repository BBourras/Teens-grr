<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\CommentStatus;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class CommentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommentRepository $commentRepository,
    ) {}

    /*
     |--------------------------------------------------------------------------
     | CREATE
     |--------------------------------------------------------------------------
     */

    public function create(Comment $comment, Post $post, User $author): void
    {
        // On empêche commentaire sur post supprimé
        if ($post->getDeletedAt() !== null) {
            throw new \LogicException('Impossible de commenter un post supprimé.');
        }

        $comment->setAuthor($author);
        $comment->setPost($post);
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setStatus(CommentStatus::PUBLISHED);

        $this->em->persist($comment);
        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | UPDATE
     |--------------------------------------------------------------------------
     */

    public function update(Comment $comment): void
    {
        $comment->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | DELETE (SOFT DELETE)
     |--------------------------------------------------------------------------
     */

    public function delete(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::DELETED);
        $comment->setDeletedAt(new \DateTimeImmutable());

        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | QUERY BUILDERS
     |--------------------------------------------------------------------------
     */

    public function getPublishedForPostQueryBuilder(Post $post): QueryBuilder
    {
        return $this->commentRepository->createQueryBuilder('c')
            ->andWhere('c.post = :post')
            ->andWhere('c.status = :status')
            ->setParameter('post', $post)
            ->setParameter('status', CommentStatus::PUBLISHED)
            ->orderBy('c.createdAt', 'ASC');
    }

    /*
     |--------------------------------------------------------------------------
     | PAGINATION
     |--------------------------------------------------------------------------
     */

    public function getPaginatedForPost(
        Post $post,
        int $page = 1,
        int $limit = 20
    ): array {
        $qb = $this->getPublishedForPostQueryBuilder($post);

        $offset = ($page - 1) * $limit;

        $items = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $countQb = clone $qb;
        $countQb->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->select('COUNT(c.id)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => (int) ceil($total / $limit),
        ];
    }
}
