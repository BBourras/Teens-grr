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

#[Route('/posts')]
class PostController extends AbstractController
{
    public function __construct(
        private PostService $postService,
        private Security $security
    ) {}

    #[Route('/', name: 'post_list', methods: ['GET'])]
    public function list(): Response
    {
        $posts = $this->postService->getLatest(10); // ou pagination via getPaginated
        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->create($post, $this->security->getUser());
            $this->addFlash('success', 'Post créé avec succès.');
            return $this->redirectToRoute('post_show', ['postId' => $post->getId()]);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{postId}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{postId}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $post);

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->update($post);
            $this->addFlash('success', 'Post mis à jour avec succès.');
            return $this->redirectToRoute('post_show', ['postId' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/{postId}/delete', name: 'post_delete', methods: ['POST', 'DELETE'])]
    public function delete(Post $post): Response
    {
        $this->denyAccessUnlessGranted('POST_DELETE', $post);

        $this->postService->delete($post);
        $this->addFlash('success', 'Post supprimé avec succès.');

        return $this->redirectToRoute('post_list');
    }
}