<?php

namespace App\Entity;

use App\Repository\PostRepository;
use App\Enum\PostStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(enumType: PostStatus::class)]
    private PostStatus $status;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\User $author = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: \App\Entity\Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: \App\Entity\Vote::class, orphanRemoval: true)]
    private Collection $votes;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->status = PostStatus::PUBLISHED;
    }

    // --- Getters / Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getStatus(): PostStatus
    {
        return $this->status;
    }

    public function setStatus(PostStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getAuthor(): ?\App\Entity\User
    {
        return $this->author;
    }

    public function setAuthor(?\App\Entity\User $author): static
    {
        $this->author = $author;
        return $this;
    }

    /** @return Collection<int, \App\Entity\Comment> */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(\App\Entity\Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this); // OK car non-nullable
        }
        return $this;
    }

    public function removeComment(\App\Entity\Comment $comment): static
    {
        $this->comments->removeElement($comment);
        // plus besoin de setPost(null), post est non-nullable
        return $this;
    }

    /** @return Collection<int, \App\Entity\Vote> */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(\App\Entity\Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setPost($this);
        }
        return $this;
    }

    public function removeVote(\App\Entity\Vote $vote): static
    {
        $this->votes->removeElement($vote);
        return $this;
    }
}