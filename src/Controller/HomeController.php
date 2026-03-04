<?php

namespace App\Controller;

use App\Service\PostService;
use App\Service\VoteService;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private PostService $postService,
        private CommentService $commentService,
        private VoteService $voteService
    ) {}

    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        // --- Derniers posts publiés ---
        $latestPosts = $this->postService->getLatestQueryBuilder()
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // --- Posts les plus populaires ---
        $topPosts = $this->postService->getTopScoredQueryBuilder()
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // --- Préparer les votes et permissions ---
        $user = $this->getUser();
        $votesInfo = [];

        foreach (array_merge($latestPosts, $topPosts) as $post) {
            $postId = $post->getId();

            // Vérifie si l'utilisateur peut voter
            $canVote = $user
                ? $this->voteService->canVote($post, $user)
                : $this->voteService->canVoteGuest($post, $request);

            $votesInfo[$postId] = [
                'score' => $this->voteService->getScore($post),
                'canVote' => $canVote,
                'userVote' => $user ? $this->voteService->getUserVote($post, $user) : null,
            ];
        }

        return $this->render('home/index.html.twig', [
            'latestPosts' => $latestPosts,
            'topPosts' => $topPosts,
            'votesInfo' => $votesInfo,
        ]);
    }
}
