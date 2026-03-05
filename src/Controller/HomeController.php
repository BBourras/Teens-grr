<?php

namespace App\Controller;

use App\Service\PostService;
use App\Service\CommentService;
use App\Service\VoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private PostService $postService,
        private CommentService $commentService,
        private VoteService $voteService,
    ) {}

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $posts = $this->postService->getLatest(10);

        $postsWithComments = [];

        foreach ($posts as $post) {
            $postsWithComments[] = [
                'post' => $post,
                'comments' => $this->commentService->getVisibleByPost($post),
                'score' => $this->voteService->getScore($post),
            ];
        }

        return $this->render('home/index.html.twig', [
            'postsData' => $postsWithComments,
        ]);
    }
}