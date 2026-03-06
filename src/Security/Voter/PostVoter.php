<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostVoter extends Voter
{
    public const VIEW = 'POST_VIEW';
    public const EDIT = 'POST_EDIT';
    public const DELETE = 'POST_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        $post = $subject;

        // Bloquer tout utilisateur suspendu
        if ($user && in_array('ROLE_BANNED', $user->getRoles(), true)) {
            return false;
        }

        // Admin / Modérateur peut tout faire
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($post),
            self::EDIT => $this->canEdit($post, $user),
            self::DELETE => $this->canDelete($post, $user),
            default => false,
        };
    }

    private function canView(Post $post): bool
    {
        return $post->getStatus() === PostStatus::PUBLISHED;
    }

    private function canEdit(Post $post, ?User $user): bool
    {
        return $user
            && $post->getAuthor() === $user
            && $post->getStatus() !== PostStatus::DELETED;
    }

    private function canDelete(Post $post, ?User $user): bool
    {
        return $user
            && $post->getAuthor() === $user
            && $post->getStatus() !== PostStatus::DELETED;
    }
}