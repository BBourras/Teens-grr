<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use App\Enum\VoteType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer les votes.
 */
class VoteService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Vérifie si un utilisateur ou un invité peut voter
     */
    public function canVote(Post $post, ?User $user, ?string $guestIp = null): bool
    {
        $repository = $this->em->getRepository(Vote::class);

        if ($user) {
            return $repository->findOneBy(['post' => $post, 'user' => $user]) === null;
        }

        if ($guestIp) {
            $oneDayAgo = new \DateTimeImmutable('-24 hours');

            $existingVote = $repository
                ->createQueryBuilder('v')
                ->where('v.post = :post')
                ->andWhere('v.guestIpHash = :ip')
                ->andWhere('v.createdAt >= :since')
                ->setParameter('post', $post)
                ->setParameter('ip', $guestIp)
                ->setParameter('since', $oneDayAgo)
                ->getQuery()
                ->getOneOrNullResult();

            return $existingVote === null;
        }

        return false;
    }

    /**
     * Récupère le vote existant d'un utilisateur
     */
    public function getUserVote(Post $post, User $user): ?Vote
    {
        return $this->em->getRepository(Vote::class)
            ->findOneBy(['post' => $post, 'user' => $user]);
    }

    /**
     * Applique un vote (toggle / changement de type)
     */
    public function vote(Post $post, ?User $user, VoteType $type, ?string $guestIp = null): void
    {
        $repository = $this->em->getRepository(Vote::class);

        $existingVote = null;
        if ($user) {
            $existingVote = $repository->findOneBy(['post' => $post, 'user' => $user]);
        } elseif ($guestIp) {
            $existingVote = $repository->findOneBy(['post' => $post, 'guestIpHash' => $guestIp]);
        }

        // CAS 1 : Aucun vote existant → création
        if (!$existingVote) {
            $vote = new Vote();
            $user ? $vote->setUser($user) : $vote->setGuestIpHash($guestIp);

            $vote->setPost($post)
                 ->setType($type)
                 ->setCreatedAt(new \DateTimeImmutable());

            $post->incrementReactionScore($type->weight());

            $this->em->persist($vote);
            $this->em->flush();
            return;
        }

        // CAS 2 : Même vote → suppression (toggle)
        if ($existingVote->getType() === $type) {
            $post->incrementReactionScore(-$type->weight());
            $this->em->remove($existingVote);
            $this->em->flush();
            return;
        }

        // CAS 3 : Changement de vote
        $post->incrementReactionScore(-$existingVote->getType()->weight());
        $existingVote->setType($type)->setCreatedAt(new \DateTimeImmutable());
        $post->incrementReactionScore($type->weight());
        $this->em->flush();
    }

    /**
     * Supprime un vote existant
     */
    public function removeVote(Vote $vote): void
    {
        $post = $vote->getPost();
        $post->incrementReactionScore(-$vote->getType()->weight());
        $this->em->remove($vote);
        $this->em->flush();
    }

    /**
     * Retourne le nombre de votes par type pour un post
     */
    public function getScore(Post $post): array
    {
        $votes = $post->getVotes();
        $score = [];

        foreach ($votes as $vote) {
            $type = $vote->getType()->value;
            $score[$type] = ($score[$type] ?? 0) + 1;
        }

        // Assure que tous les types sont présents
        foreach (VoteType::cases() as $case) {
            $score[$case->value] = $score[$case->value] ?? 0;
        }

        return $score;
    }
}