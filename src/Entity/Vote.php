<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\VoteType;
use App\Repository\VoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant une réaction (vote) sur un Post.
 *
 * Règles métier :
 * - 1 vote maximum par utilisateur et par post
 * - 1 vote maximum par invité (guestKey) et par post
 * - Possibilité de modification (update du type)
 */
#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(
    uniqueConstraints: [

        // ✅ 1 réaction par user et par post
        new ORM\UniqueConstraint(
            name: 'uniq_vote_user_post',
            columns: ['user_id', 'post_id']
        ),

        // ✅ 1 réaction par guest et par post
        new ORM\UniqueConstraint(
            name: 'uniq_vote_guest_post',
            columns: ['guest_key', 'post_id']
        ),
    ],
    indexes: [

        // Accélère les requêtes par post (affichage score)
        new ORM\Index(name: 'idx_vote_post', columns: ['post_id']),

        // Utile pour tri / modération / stats
        new ORM\Index(name: 'idx_vote_created_at', columns: ['created_at']),

        // Recherche rapide des votes invités
        new ORM\Index(name: 'idx_vote_guest_key', columns: ['guest_key']),

        // Optimisation requête "1 vote par 24h"
        new ORM\Index(
            name: 'idx_vote_guest_post_created',
            columns: ['guest_key', 'post_id', 'created_at']
        ),
    ]
)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Utilisateur connecté (nullable pour les invités).
     * Suppression en cascade si user supprimé.
     */
    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * Post concerné par le vote.
     * Suppression en cascade si post supprimé.
     */
    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    /**
     * Type de vote (LIKE / LAUGH / ANGRY).
     */
    #[ORM\Column(enumType: VoteType::class)]
    private VoteType $type;

    /**
     * Identifiant invité (UUID stocké en cookie).
     * Permet de limiter à 1 vote / post pour les non-connectés.
     *
     * ⚠ Ne jamais utiliser l’IP brute comme identifiant principal.
     */
    #[ORM\Column(name: 'guest_key', length: 64, nullable: true)]
    private ?string $guestKey = null;

    /**
     * Hash d'IP (optionnel, RGPD compliant).
     * Utilisé pour limiter à 1 vote / 24h si nécessaire.
     */
    #[ORM\Column(name: 'guest_ip_hash', length: 128, nullable: true)]
    private ?string $guestIpHash = null;

    /**
     * Date de création du vote.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /**
     * Définit automatiquement createdAt à l’insertion.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    // ================================
    // Getters & Setters
    // ================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getType(): VoteType
    {
        return $this->type;
    }

    public function setType(VoteType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getGuestKey(): ?string
    {
        return $this->guestKey;
    }

    public function setGuestKey(?string $guestKey): static
    {
        $this->guestKey = $guestKey;
        return $this;
    }

    public function getGuestIpHash(): ?string
    {
        return $this->guestIpHash;
    }

    public function setGuestIpHash(?string $guestIpHash): static
    {
        $this->guestIpHash = $guestIpHash;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}