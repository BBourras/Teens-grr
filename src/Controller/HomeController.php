<?php

namespace App\Controller;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PostService $postService): Response
    {
        return $this->render('home/index.html.twig', [
            'latestPosts' => $postService->getLatest(5),
            'topPosts' => $postService->getTopScored(5),
        ]);
    }
}
