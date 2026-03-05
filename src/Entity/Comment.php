<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CommentStatus;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(
    indexes: [
        new ORM\Index(name: 'idx_comment_post', columns: ['post_id']),
        new ORM\Index(name: 'idx_comment_status', columns: ['status']),
        new ORM\Index(name: 'idx_comment_created_at', columns: ['created_at']),
        new ORM\Index(name: 'idx_comment_author', columns: ['author_id']),
    ]
)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(enumType: CommentStatus::class)]
    private CommentStatus $status = CommentStatus::PUBLISHED;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    /** @var Collection<int, Report> */
    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: Report::class)]
    private Collection $reports;

    /** @var Collection<int, ModerationActionLog> */
    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: ModerationActionLog::class)]
    private Collection $moderationLogs;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->moderationLogs = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
        $this->status ??= CommentStatus::PUBLISHED;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getDeletedAt(): ?\DateTimeImmutable { return $this->deletedAt; }
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static { $this->deletedAt = $deletedAt; return $this; }

    public function getStatus(): CommentStatus { return $this->status; }
    public function setStatus(CommentStatus $status): static { $this->status = $status; return $this; }

    public function getAuthor(): User { return $this->author; }
    public function setAuthor(User $author): static { $this->author = $author; return $this; }

    public function getPost(): Post { return $this->post; }
    public function setPost(Post $post): static { $this->post = $post; return $this; }

    /** @return Collection<int, Report> */
    public function getReports(): Collection { return $this->reports; }

    /** @return Collection<int, ModerationActionLog> */
    public function getModerationLogs(): Collection { return $this->moderationLogs; }
}
