<?php

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\User;
use App\Enum\CommentStatus;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    public const EDIT = 'COMMENT_EDIT';
    public const DELETE = 'COMMENT_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Comment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        $comment = $subject;

        // Bloquer tout utilisateur suspendu
        if ($user && in_array('ROLE_BANNED', $user->getRoles(), true)) {
            return false;
        }

        // Admin / Modérateur peut tout faire
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($comment, $user),
            self::DELETE => $this->canDelete($comment, $user),
            default => false,
        };
    }

    private function canEdit(Comment $comment, User $user): bool
    {
        return $comment->getAuthor() === $user
            && $comment->getStatus() !== CommentStatus::DELETED;
    }

    private function canDelete(Comment $comment, User $user): bool
    {
        return $comment->getAuthor() === $user
            && $comment->getStatus() !== CommentStatus::DELETED;
    }
}