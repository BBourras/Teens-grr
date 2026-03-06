<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use App\Enum\VoteType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function findUserVote(Post $post, ?User $user, ?string $guestKey): ?Vote
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.post = :post')
            ->setParameter('post', $post);

        if ($user) {
            $qb->andWhere('v.user = :user')
               ->setParameter('user', $user);
        } else {
            $qb->andWhere('v.guestKey = :guestKey')
               ->setParameter('guestKey', $guestKey);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countRecentVotesByGuest(Post $post, string $guestKey, \DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.post = :post')
            ->andWhere('v.guestKey = :guestKey')
            ->andWhere('v.createdAt >= :since')
            ->setParameter('post', $post)
            ->setParameter('guestKey', $guestKey)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getScore(Post $post): array
    {
        $results = $this->createQueryBuilder('v')
            ->select('v.type as type, COUNT(v.id) as count')
            ->where('v.post = :post')
            ->groupBy('v.type')
            ->setParameter('post', $post)
            ->getQuery()
            ->getResult();

        $scores = [];
        foreach ($results as $row) {
            $scores[VoteType::from($row['type'])] = (int)$row['count'];
        }

        return $scores;
    }
}