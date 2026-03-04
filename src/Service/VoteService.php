<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use App\Enum\VoteType;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class VoteService
{
    public function __construct(
        private EntityManagerInterface $em,
        private VoteRepository $voteRepository,
    ) {}

    public function vote(
        Post $post,
        VoteType $type,
        ?User $user = null,
        ?string $ipAddress = null
    ): void {

        // Cas utilisateur connecté
        if ($user) {

            $existingVote = $this->voteRepository->findOneBy([
                'post' => $post,
                'user' => $user
            ]);

            if ($existingVote) {
                // Si même type → on ne fait rien
                if ($existingVote->getType() === $type) {
                    return;
                }

                // Sinon on change la réaction
                $existingVote->setType($type);
                $this->em->flush();
                return;
            }

            $vote = new Vote();
            $vote->setPost($post)
                 ->setUser($user)
                 ->setType($type);

            $post->incrementVoteCount();

            $this->em->persist($vote);
            $this->em->flush();

            return;
        }

        // Cas anonyme
        if ($ipAddress) {

            $existingVote = $this->voteRepository->findOneBy([
                'post' => $post,
                'ipAddress' => $ipAddress
            ]);

            if ($existingVote) {
                return; // 1 vote max
            }

            $vote = new Vote();
            $vote->setPost($post)
                 ->setIpAddress($ipAddress)
                 ->setType($type);

            $post->incrementVoteCount();

            $this->em->persist($vote);
            $this->em->flush();
        }
    }
}