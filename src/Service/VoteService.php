<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use App\Enum\VoteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class VoteService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Vérifie si un utilisateur connecté peut voter sur ce post
     */
    public function canVote(Post $post, User $user): bool
    {
        /** @var Vote|null $existingVote */
        $existingVote = $this->em->getRepository(Vote::class)
            ->findOneBy(['post' => $post, 'user' => $user]);

        return $existingVote === null;
    }

    /**
     * Vérifie si un utilisateur non connecté peut voter (1 vote / 24h par IP)
     */
    public function canVoteGuest(Post $post, Request $request): bool
    {
        $ip = $request->getClientIp();
        if (!$ip) {
            return false;
        }

        $oneDayAgo = new \DateTimeImmutable('-24 hours');

        /** @var Vote|null $existingVote */
        $existingVote = $this->em->getRepository(Vote::class)
            ->createQueryBuilder('v')
            ->where('v.post = :post')
            ->andWhere('v.guestIp = :ip')
            ->andWhere('v.createdAt >= :since')
            ->setParameter('post', $post)
            ->setParameter('ip', $ip)
            ->setParameter('since', $oneDayAgo)
            ->getQuery()
            ->getOneOrNullResult();

        return $existingVote === null;
    }

    /**
     * Retourne le score du post par type d'emoji
     */
    public function getScore(Post $post): array
    {
        $votes = $post->getVotes();
        $score = [];

        foreach ($votes as $vote) {
            $type = $vote->getType()->value; // 'like', 'laugh', 'angry'
            $score[$type] = ($score[$type] ?? 0) + 1;
        }

        // Initialiser les types manquants
        foreach (VoteType::allValues() as $value) {
            $score[$value] = $score[$value] ?? 0;
        }

        return $score;
    }

    /**
     * Retourne le vote existant de l'utilisateur sur ce post
     */
    public function getUserVote(Post $post, User $user): ?Vote
    {
        /** @var Vote|null $vote */
        $vote = $this->em->getRepository(Vote::class)
            ->findOneBy(['post' => $post, 'user' => $user]);

        return $vote;
    }

    /**
     * Crée ou met à jour un vote
     */
    public function vote(Post $post, ?User $user, VoteType $type, ?string $guestIp = null): Vote
    {
        if ($user) {
            $vote = $this->getUserVote($post, $user) ?? new Vote();
            $vote->setUser($user);
        } else {
            $vote = new Vote();
            $vote->setGuestIp($guestIp);
        }

        $vote->setPost($post);
        $vote->setType($type);
        $vote->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($vote);
        $this->em->flush();

        return $vote;
    }

    /**
     * Supprime un vote (ex: undo)
     */
    public function removeVote(Vote $vote): void
    {
        $this->em->remove($vote);
        $this->em->flush();
    }
}