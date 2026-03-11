<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service métier central pour la gestion des posts.
 *
 * Contient :
 * - Création / suppression / restauration
 * - Récupération des listes publiques
 * - Classements éditoriaux (Top du moment / Légendes)
 */
class PostService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PostRepository $postRepository,
    ) {}

    /**
     * ============================================
     * Création d’un post
     * ============================================
     * - Assigne l’auteur
     * - Définit le statut initial
     * - Persiste en base
     */
    public function create(Post $post, User $author): void
    {
        $post->setAuthor($author);
        $post->setStatus(PostStatus::PUBLISHED);

        $this->em->persist($post);
        $this->em->flush();
    }

    /**
     * ============================================
     * Mise à jour
     * ============================================
     * Doctrine détecte automatiquement les changements.
     */
    public function update(Post $post): void
    {
        $this->em->flush();
    }

    /**
     * ============================================
     * Suppression logique (soft delete)
     * ============================================
     * On ne supprime pas physiquement en base.
     */
    public function delete(Post $post): void
    {
        $post->setStatus(PostStatus::DELETED);
        $post->setDeletedAt(new \DateTimeImmutable());

        $this->em->flush();
    }

    /**
     * Suppression définitive en base.
     */
    public function hardDelete(Post $post): void
    {
        $this->em->remove($post);
        $this->em->flush();
    }

    /**
     * ============================================
     * Derniers posts publiés
     * ============================================
     */
    public function getLatest(int $limit = 10): array
    {
        return $this->postRepository->findLatest($limit);
    }

    /**
     * ============================================
     * Classement simple par reactionScore
     * ============================================
     * (score dénormalisé stocké en base)
     */
    public function getTopScored(int $limit = 5): array
    {
        return $this->postRepository->findBy(
            ['status' => PostStatus::PUBLISHED],
            ['reactionScore' => 'DESC'],
            $limit
        );
    }

    /**
     * ============================================
     * 🔥 TOP DU MOMENT
     * ============================================
     *
     * Classement intelligent :
     * - Favorise Laugh + Disillusioned
     * - Pénalise Angry
     * - Applique un déclin temporel
     *
     * Algorithme invisible côté utilisateur.
     */
    public function getTopDuMoment(int $limit = 10): array
    {
        return $this->postRepository->findTopDuMoment($limit);
    }

    /**
     * ============================================
     * 🏛 LÉGENDES
     * ============================================
     *
     * Classement durable :
     * - Basé uniquement sur l'humour
     * - Pas de déclin temporel
     */
    public function getLegendes(int $limit = 10): array
    {
        return $this->postRepository->findLegendes($limit);
    }

    /**
     * ============================================
     * Visibilité d’un post
     * ============================================
     * - Visible si publié
     * - Visible pour modérateur
     */
    public function isVisible(Post $post, ?User $user): bool
    {
        if ($post->getStatus() === PostStatus::PUBLISHED) {
            return true;
        }

        if ($user && in_array('ROLE_MODERATOR', $user->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * ============================================
     * Posts auto-masqués (modération automatique)
     * ============================================
     */
    public function getAutoHidden(): array
    {
        return $this->postRepository->findBy(
            ['status' => PostStatus::AUTO_HIDDEN],
            ['createdAt' => 'DESC']
        );
    }

    /**
     * ============================================
     * Restaurer un post
     * ============================================
     */
    public function restore(Post $post): void
    {
        $post->setStatus(PostStatus::PUBLISHED);
        $this->em->flush();
    }
}