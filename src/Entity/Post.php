<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(
    indexes: [
        new ORM\Index(name: 'idx_post_status', columns: ['status']),
        new ORM\Index(name: 'idx_post_created_at', columns: ['created_at']),
        new ORM\Index(name: 'idx_post_author', columns: ['author_id']),
    ]
)]
class Post
{
    // =======================
    // IDENTIFIANT
    // =======================
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // =======================
    // TITRE ET CONTENU
    // =======================
    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    // =======================
    // DATES
    // =======================
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    // =======================
    // STATUT
    // =======================
    #[ORM\Column(enumType: PostStatus::class)]
    private PostStatus $status = PostStatus::PUBLISHED;

    // =======================
    // COMPTEURS DENORMALISES
    // =======================
    #[ORM\Column(options: ['default' => 0])]
    private int $commentCount = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $reportCount = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $reactionScore = 0;

    // =======================
    // RELATIONS
    // =======================
    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    /** @var Collection<int, Vote> */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Vote::class)]
    private Collection $votes;

    /** @var Collection<int, Report> */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Report::class)]
    private Collection $reports;

    /** @var Collection<int, ModerationActionLog> */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: ModerationActionLog::class)]
    private Collection $moderationLogs;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->moderationLogs = new ArrayCollection();
    }

    // =======================
    // LIFECYCLE CALLBACKS
    // =======================
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
        $this->status ??= PostStatus::PUBLISHED;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // =======================
    // GETTERS / SETTERS
    // =======================
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getDeletedAt(): ?\DateTimeImmutable { return $this->deletedAt; }
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static { $this->deletedAt = $deletedAt; return $this; }

    public function getStatus(): PostStatus { return $this->status; }
    public function setStatus(PostStatus $status): static { $this->status = $status; return $this; }

    public function getAuthor(): User { return $this->author; }
    public function setAuthor(User $author): static { $this->author = $author; return $this; }

    public function getCommentCount(): int { return $this->commentCount; }
    public function setCommentCount(int $count): static { $this->commentCount = $count; return $this; }
    public function incrementCommentCount(int $by = 1): static { $this->commentCount += $by; return $this; }
    public function decrementCommentCount(int $by = 1): static { $this->commentCount = max(0, $this->commentCount - $by); return $this; }

    public function getReportCount(): int { return $this->reportCount; }
    public function setReportCount(int $count): static { $this->reportCount = $count; return $this; }
    public function incrementReportCount(int $by = 1): static { $this->reportCount += $by; return $this; }

    public function getReactionScore(): int { return $this->reactionScore; }
    public function setReactionScore(int $score): static { $this->reactionScore = $score; return $this; }
    public function incrementReactionScore(int $by = 1): static { $this->reactionScore += $by; return $this; }

    /** @return Collection<int, Comment> */
    public function getComments(): Collection { return $this->comments; }
    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }
        return $this;
    }

    /** @return Collection<int, Vote> */
    public function getVotes(): Collection { return $this->votes; }
    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setPost($this);
        }
        return $this;
    }

    /** @return Collection<int, Report> */
    public function getReports(): Collection { return $this->reports; }

    /** @return Collection<int, ModerationActionLog> */
    public function getModerationLogs(): Collection { return $this->moderationLogs; }
}