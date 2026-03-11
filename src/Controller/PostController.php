<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\PostFormType;
use App\Form\CommentFormType;
use App\Service\PostService;
use App\Service\CommentService;
use App\Service\VoteService;
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
        private VoteService $voteService,
        private Security $security
    ) {}

    /**
     * =====================================================
     * 📰 LISTE DES POSTS (Accueil / Flux principal)
     * =====================================================
     *
     * - Affiche les derniers posts publiés
     * - Passe voteService pour affichage des compteurs emoji
     */
    #[Route('/', name: 'post_list', methods: ['GET'])]
    public function list(): Response
    {
        $posts = $this->postService->getLatest(10);

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
            'voteService' => $this->voteService, // utilisé dans Twig pour getScore(post)
        ]);
    }

    /**
     * =====================================================
     * 🔥 TOP DU MOMENT
     * =====================================================
     *
     * Classement intelligent :
     * - Favorise 😏 + 😂
     * - Pénalise 😡
     * - Boost les posts récents
     *
     * Algorithme invisible côté utilisateur.
     */
    #[Route('/top', name: 'post_top', methods: ['GET'])]
    public function top(): Response
    {
        $posts = $this->postService->getTopDuMoment(20);

        return $this->render('post/top.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * =====================================================
     * 🏛 LÉGENDES
     * =====================================================
     *
     * Classement durable :
     * - Basé uniquement sur l'humour
     * - Pas de déclin temporel
     */
    #[Route('/legendes', name: 'post_legendes', methods: ['GET'])]
    public function legendes(): Response
    {
        $posts = $this->postService->getLegendes(20);

        return $this->render('post/legendes.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * =====================================================
     * ✍️ CRÉATION D’UN POST
     * =====================================================
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
                'id' => $post->getId()
            ]);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * =====================================================
     * 📄 AFFICHAGE D’UN POST
     * =====================================================
     *
     * - Vérifie la visibilité
     * - Charge les commentaires visibles
     * - Prépare formulaire commentaire si connecté
     * - Charge les compteurs emoji
     */
    #[Route('/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $user = $this->getUser();

        // Vérification visibilité (modération incluse)
        if (!$this->postService->isVisible($post, $user)) {
            throw $this->createNotFoundException();
        }

        $comments = $this->commentService
            ->getVisibleByPost($post, $user);

        $formView = null;

        if ($user) {
            $comment = new Comment();
            $form = $this->createForm(CommentFormType::class, $comment);
            $formView = $form->createView();
        }

        // Récupération des compteurs par emoji
        $postVotes = $this->voteService->getScore($post);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $formView,
            'postVotes' => $postVotes,
        ]);
    }

    /**
     * =====================================================
     * ✏️ ÉDITION D’UN POST
     * =====================================================
     */
    #[Route('/{id}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $post);

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->postService->update($post);

            $this->addFlash('success', 'Post mis à jour.');

            return $this->redirectToRoute('post_show', [
                'id' => $post->getId()
            ]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    /**
     * =====================================================
     * 🗑 SUPPRESSION LOGIQUE (Soft Delete)
     * =====================================================
     */
    #[Route('/{id}/delete', name: 'post_delete', methods: ['POST'])]
    public function delete(Post $post): Response
    {
        $this->denyAccessUnlessGranted('POST_DELETE', $post);

        $this->postService->delete($post);

        $this->addFlash('success', 'Post supprimé.');

        return $this->redirectToRoute('post_list');
    }
}