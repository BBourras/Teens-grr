<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\Table(
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_user_post_report', columns: ['user_id', 'post_id']),
        new ORM\UniqueConstraint(name: 'uniq_user_comment_report', columns: ['user_id', 'comment_id']),
    ],
    indexes: [
        new ORM\Index(name: 'idx_report_post', columns: ['post_id']),
        new ORM\Index(name: 'idx_report_comment', columns: ['comment_id']),
        new ORM\Index(name: 'idx_report_created_at', columns: ['created_at']),
        new ORM\Index(name: 'idx_report_user', columns: ['user_id']),
    ]
)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Comment $comment = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function assertExactlyOneTarget(): void
    {
        $hasPost = $this->post !== null;
        $hasComment = $this->comment !== null;

        if ($hasPost === $hasComment) {
            // true/true ou false/false => invalide
            throw new \LogicException('Un report doit cibler soit un post, soit un commentaire (exactement un).');
        }
    }

    public function getId(): ?int { return $this->id; }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): static { $this->reason = $reason; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }

    public function getPost(): ?Post { return $this->post; }
    public function setPost(?Post $post): static { $this->post = $post; return $this; }

    public function getComment(): ?Comment { return $this->comment; }
    public function setComment(?Comment $comment): static { $this->comment = $comment; return $this; }

    public function isForPost(): bool { return $this->post !== null; }
    public function isForComment(): bool { return $this->comment !== null; }
}
