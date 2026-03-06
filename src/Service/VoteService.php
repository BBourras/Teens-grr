<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use App\Enum\VoteType;
use Doctrine\ORM\EntityManagerInterface;

class VoteService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function canVote(Post $post, ?User $user, ?string $guestIp = null): bool
    {
        if ($user) {
            return $this->em->getRepository(Vote::class)
                        ->findOneBy(['post' => $post, 'user' => $user]) === null;
        }

        if ($guestIp) {
            $oneDayAgo = new \DateTimeImmutable('-24 hours');
            $existingVote = $this->em->getRepository(Vote::class)
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

    public function getUserVote(Post $post, User $user): ?Vote
    {
        return $this->em->getRepository(Vote::class)
            ->findOneBy(['post' => $post, 'user' => $user]);
    }

    public function vote(Post $post, ?User $user, VoteType $type, ?string $guestIp = null): Vote
    {
        $vote = $user
            ? $this->getUserVote($post, $user) ?? new Vote()
            : new Vote();

        if ($user) {
            $vote->setUser($user);
        } else {
            $vote->setGuestIpHash($guestIp);
        }

        $vote->setPost($post)
             ->setType($type)
             ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($vote);
        $this->em->flush();

        return $vote;
    }

    public function removeVote(Vote $vote): void
    {
        $this->em->remove($vote);
        $this->em->flush();
    }

    public function getScore(Post $post): array
    {
        $votes = $post->getVotes();
        $score = [];

        foreach ($votes as $vote) {
            $type = $vote->getType()->value;
            $score[$type] = ($score[$type] ?? 0) + 1;
        }

        foreach (VoteType::cases() as $case) {
            $score[$case->value] = $score[$case->value] ?? 0;
        }

        return $score;
    }
}