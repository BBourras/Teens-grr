<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    public function __construct(
        private PostService $postService,
        private Security $security
    ) {}

    #[Route('/posts/{id}', name: 'post_show')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/posts/new', name: 'post_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Post();

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->postService->create(
                $post,
                $this->security->getUser()
            );

            return $this->redirectToRoute('post_show', [
                'id' => $post->getId()
            ]);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form
        ]);
    }
}