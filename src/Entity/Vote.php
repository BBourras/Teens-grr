<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use App\Enum\VoteType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?\App\Entity\User $user = null; // nullable pour guests

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    private \App\Entity\Post $post;

    #[ORM\Column(enumType: VoteType::class)]
    private VoteType $type;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $guestIp = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?\App\Entity\User
    {
        return $this->user;
    }

    public function setUser(?\App\Entity\User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPost(): \App\Entity\Post
    {
        return $this->post;
    }

    public function setPost(\App\Entity\Post $post): static
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

    public function getGuestIp(): ?string
    {
        return $this->guestIp;
    }

    public function setGuestIp(?string $guestIp): static
    {
        $this->guestIp = $guestIp;
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
}