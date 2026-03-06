<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Vote;
use App\Entity\User;
use App\Enum\VoteType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function findUserVote(Post $post, ?User $user, ?string $ip): ?Vote
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.post = :post')
            ->setParameter('post', $post);

        if ($user) {
            $qb->andWhere('v.user = :user')
               ->setParameter('user', $user);
        } else {
            $qb->andWhere('v.ipAddress = :ip')
               ->setParameter('ip', $ip);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countRecentVotesByIp(Post $post, string $ip, \DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.post = :post')
            ->andWhere('v.ipAddress = :ip')
            ->andWhere('v.createdAt >= :since')
            ->setParameter('post', $post)
            ->setParameter('ip', $ip)
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
            $scores[$row['type']] = (int) $row['count'];
        }

        return $scores;
    }
}