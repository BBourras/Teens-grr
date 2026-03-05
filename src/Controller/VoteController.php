<?php

namespace App\Controller;

use App\Entity\Post;
use App\Enum\VoteType;
use App\Service\VoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class VoteController extends AbstractController
{
    public function __construct(
        private VoteService $voteService,
        private Security $security
    ) {}

    #[Route('/posts/{id}/votes/{type}', name: 'post_vote', methods: ['POST'])]
    public function vote(Post $post, string $type, Request $request): JsonResponse
    {
        $voteType = VoteType::tryFrom($type);

        if (!$voteType) {
            return $this->json(['error' => 'Invalid vote type'], 400);
        }

        $user = $this->security->getUser();
        $ip = $request->getClientIp();

        if (!$this->voteService->canVote($post, $user, $ip)) {
            return $this->json(['error' => 'Vote not allowed'], 403);
        }

        $vote = $this->voteService->vote($post, $user, $voteType, $ip);

        return $this->json([
            'success' => true,
            'scores' => $this->voteService->getScore($post),
            'userVote' => $vote?->getType()->value
        ]);
    }
}