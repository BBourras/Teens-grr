<?php

namespace App\Controller;

use App\Entity\Post;
use App\Enum\VoteType;
use App\Service\VoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/votes')]class VoteController extends AbstractController
{
    public function __construct(private VoteService $voteService) {}

    #[Route('/posts/{id}/vote', name: 'vote_post', methods: ['POST'])]
    public function vote(Post $post, Request $request): Response
    {
        $user = $this->getUser();
        $voteType = $request->request->get('type');

        if (!in_array($voteType, array_map(fn(VoteType $v) => $v->value, VoteType::cases()))) {
            $this->addFlash('error', 'Type de vote invalide.');
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        $this->voteService->vote(
            $post,
            $user ?: null,
            VoteType::from($voteType),
            $request->getClientIp()
        );

        $this->addFlash('success', 'Votre vote a été pris en compte !');

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }
}