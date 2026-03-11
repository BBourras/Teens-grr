<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use App\Enum\VoteType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository dédié aux votes.
 * Gère :
 * - récupération vote utilisateur
 * - limitation invités
 * - agrégations (score par type)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    /**
     * Récupère le vote d’un utilisateur ou invité pour un post donné.
     */
    public function findUserVote(Post $post, ?User $user, ?string $guestKey): ?Vote
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.post = :post')
            ->setParameter('post', $post);

        if ($user) {
            $qb->andWhere('v.user = :user')
               ->setParameter('user', $user);
        } elseif ($guestKey) {
            $qb->andWhere('v.guestKey = :guestKey')
               ->setParameter('guestKey', $guestKey);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Limitation : nombre de votes récents d’un invité
     * (ex: 1 vote / 24h)
     */
    public function countRecentVotesByGuest(
        Post $post,
        string $guestKey,
        \DateTimeInterface $since
    ): int {
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

    /**
     * Retourne le nombre de votes par type pour un post.
     * Format :
     * [
     *   'laugh' => 12,
     *   'angry' => 3,
     *   'disillusioned' => 5
     * ]
     */
    public function getScoreByType(Post $post): array
    {
        $results = $this->createQueryBuilder('v')
            ->select('v.type AS type, COUNT(v.id) AS count')
            ->where('v.post = :post')
            ->groupBy('v.type')
            ->setParameter('post', $post)
            ->getQuery()
            ->getResult();

        $scores = [];

        foreach ($results as $row) {
            $scores[$row['type']] = (int) $row['count'];
        }

        // Garantit que tous les types existent
        foreach (VoteType::cases() as $case) {
            $scores[$case->value] = $scores[$case->value] ?? 0;
        }

        return $scores;
    }
}