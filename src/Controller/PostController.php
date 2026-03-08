<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\PostFormType;
use App\Form\CommentFormType;
use App\Service\PostService;
use App\Service\CommentService;
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
        private CommentService $commentService,
        private Security $security
    ) {}

    /**
     * Liste des posts publics.
     */
    #[Route('/', name: 'post_list', methods: ['GET'])]
    public function list(): Response
    {
        // On récupère les derniers posts publiés
        $posts = $this->postService->getLatest(10);

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * Création d’un nouveau post.
     * Accessible uniquement aux utilisateurs connectés.
     */
    #[Route('/new', name: 'post_new', methods: ['GET', 'POST'])]
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

            $this->addFlash('success', 'Post créé avec succès.');

            return $this->redirectToRoute('post_show', [
                'postId' => $post->getId()
            ]);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affichage d’un post.
     * - Vérifie la visibilité (statut + rôle)
     * - Charge les commentaires visibles
     * - Prépare le formulaire de commentaire si connecté
     */
    #[Route('/{postId}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $user = $this->getUser();

        // Vérifie si le post est visible pour cet utilisateur
        if (!$this->postService->isVisible($post, $user)) {
            throw $this->createNotFoundException();
        }

        // Récupération des commentaires visibles
        $comments = $this->commentService->getVisibleByPost($post, $user);

        // Préparation du formulaire de commentaire (si connecté)
        $formView = null;

        if ($user) {
            $comment = new Comment();
            $form = $this->createForm(CommentFormType::class, $comment);
            $formView = $form->createView();
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $formView,
        ]);
    }

    /**
     * Édition d’un post.
     * La permission est gérée par un Voter (POST_EDIT).
     */
    #[Route('/{postId}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $post);

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->postService->update($post);

            $this->addFlash('success', 'Post mis à jour avec succès.');

            return $this->redirectToRoute('post_show', [
                'postId' => $post->getId()
            ]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    /**
     * Suppression logique d’un post.
     * Permission gérée par Voter (POST_DELETE).
     */
    #[Route('/{postId}/delete', name: 'post_delete', methods: ['POST', 'DELETE'])]
    public function delete(Post $post): Response
    {
        $this->denyAccessUnlessGranted('POST_DELETE', $post);

        $this->postService->delete($post);

        $this->addFlash('success', 'Post supprimé avec succès.');

        return $this->redirectToRoute('post_list');
    }
}