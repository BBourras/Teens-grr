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
    public function list(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $qb = $this->postService->getLatestQueryBuilder();

        $pagination = $this->postService->getPaginated($qb, $page, 10);

        return $this->render('post/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->create($post, $this->getUser());
            $this->addFlash('success', 'Post créé !');

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $this->denyAccessUnlessGranted('POST_VIEW', $post);

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'post_edit', methods: ['GET','POST'])]
    public function edit(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $post);

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->update($post);
            $this->addFlash('success', 'Post mis à jour !');

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/{id}/delete', name: 'post_delete', methods: ['POST', 'DELETE'])]
    public function delete(Post $post): Response
    {
        $this->denyAccessUnlessGranted('POST_DELETE', $post);

        $this->postService->delete($post);
        $this->addFlash('success', 'Post supprimé !');

        return $this->redirectToRoute('post_list');
    }
}