<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PostRepository $postRepository,
    ) {}

    /**
     * Création d’un post.
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
     * Mise à jour d’un post. Doctrine détecte automatiquement les modifications.
     */
    public function update(Post $post): void
    {
        $this->em->flush();
    }

    /**
     * Suppression logique (soft delete). On ne supprime pas physiquement en base.
     */
    public function delete(Post $post): void
    {
        $post->setStatus(PostStatus::DELETED);
        $post->setDeletedAt(new \DateTimeImmutable());

        $this->em->flush();
    }

    /**
     * Suppression définitive d’un post.
     */
    public function hardDelete(Post $post): void
    {
        $this->em->remove($post);
        $this->em->flush();
    }

    /**
     * Récupère les derniers posts publiés.
     * Utilisé pour la page d’accueil ou la liste.
     */
    public function getLatest(int $limit = 10): array
    {
        return $this->postRepository->findBy(
            ['status' => PostStatus::PUBLISHED],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    /**
     * Vérifie si un post est visible pour un utilisateur donné.
     * - Visible si PUBLISHED, Visible si modérateur, Sinon non visible
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
     * Récupère les posts auto-masqués suite aux signalements.
     */
    public function getAutoHidden(): array
    {
        return $this->postRepository->findBy(
            ['status' => PostStatus::AUTO_HIDDEN],
            ['createdAt' => 'DESC']
        );
    }

    /**
     * Restaurer un post (après suppression ou auto-hide)
     */
    public function restore(Post $post): void
    {
        $post->setStatus(PostStatus::PUBLISHED);
        $this->em->flush();
    }
}