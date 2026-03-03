<?php

namespace App\Entity;

use App\Enum\ModerationActionType;
use App\Repository\ModerationActionLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModerationActionLogRepository::class)]
class ModerationActionLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: ModerationActionType::class)]
    private ModerationActionType $actionType;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $previousStatus = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $newStatus = null;

    #[ORM\Column(nullable: true)]
    private ?string $reason = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $moderator;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Post $post = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Comment $comment = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ModerationActionType
    {
        return $this->actionType;
    }

    public function setActionType(ModerationActionType $actionType): static
    {
        $this->actionType = $actionType;
        return $this;
    }

    public function getPreviousStatus(): ?string
    {
        return $this->previousStatus;
    }

    public function setPreviousStatus(?string $previousStatus): static
    {
        $this->previousStatus = $previousStatus;
        return $this;
    }

    public function getNewStatus(): ?string
    {
        return $this->newStatus;
    }

    public function setNewStatus(?string $newStatus): static
    {
        $this->newStatus = $newStatus;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getModerator(): User
    {
        return $this->moderator;
    }

    public function setModerator(User $moderator): static
    {
        $this->moderator = $moderator;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): static
    {
        $this->comment = $comment;
        return $this;
    }
}