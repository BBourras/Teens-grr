<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\VoteType;
use App\Repository\VoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(
    uniqueConstraints: [
        // 1 réaction par user et par post (modifiable via update)
        new ORM\UniqueConstraint(name: 'uniq_vote_user_post', columns: ['user_id', 'post_id']),
    ],
    indexes: [
        new ORM\Index(name: 'idx_vote_post', columns: ['post_id']),
        new ORM\Index(name: 'idx_vote_created_at', columns: ['created_at']),
        new ORM\Index(name: 'idx_vote_guest_key', columns: ['guest_key']),
        new ORM\Index(name: 'idx_vote_guest_post_created', columns: ['guest_key', 'post_id', 'created_at']),
    ]
)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null; // nullable pour guests

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    #[ORM\Column(enumType: VoteType::class)]
    private VoteType $type;

    /**
     * Identifiant “guest” (UUID en cookie) pour limiter à 1 vote / 24h.
     */
    #[ORM\Column(name: 'guest_key', length: 64, nullable: true)]
    private ?string $guestKey = null;

    /**
     * Hash d'IP (optionnel) plutôt que l'IP brute (RGPD).
     */
    #[ORM\Column(name: 'guest_ip_hash', length: 128, nullable: true)]
    private ?string $guestIpHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getPost(): Post { return $this->post; }
    public function setPost(Post $post): static { $this->post = $post; return $this; }

    public function getType(): VoteType { return $this->type; }
    public function setType(VoteType $type): static { $this->type = $type; return $this; }

    public function getGuestKey(): ?string { return $this->guestKey; }
    public function setGuestKey(?string $guestKey): static { $this->guestKey = $guestKey; return $this; }

    public function getGuestIpHash(): ?string { return $this->guestIpHash; }
    public function setGuestIpHash(?string $guestIpHash): static { $this->guestIpHash = $guestIpHash; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
