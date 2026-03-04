<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\ModerationActionLog;
use App\Enum\PostStatus;
use App\Enum\CommentStatus;
use App\Enum\ModerationActionType;
use Doctrine\ORM\EntityManagerInterface;

class ModerationService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /*
     |--------------------------------------------------------------------------
     | POST MODERATION
     |--------------------------------------------------------------------------
     */

    public function hidePost(Post $post, User $moderator, ?string $reason = null): void
    {
        if ($post->getStatus() === PostStatus::HIDDEN_BY_MODERATOR) {
            return;
        }

        $previousStatus = $post->getStatus();

        $post->setStatus(PostStatus::HIDDEN_BY_MODERATOR);

        $this->logAction(
            actionType: ModerationActionType::MODERATOR_HIDDEN,
            moderator: $moderator,
            previousStatus: $previousStatus->value,
            newStatus: PostStatus::HIDDEN_BY_MODERATOR->value,
            post: $post,
            reason: $reason
        );

        $this->em->flush();
    }

    public function restorePost(Post $post, User $moderator, ?string $reason = null): void
    {
        if ($post->getStatus() === PostStatus::PUBLISHED) {
            return;
        }

        $previousStatus = $post->getStatus();

        $post->setStatus(PostStatus::PUBLISHED);

        $this->logAction(
            actionType: ModerationActionType::RESTORED,
            moderator: $moderator,
            previousStatus: $previousStatus->value,
            newStatus: PostStatus::PUBLISHED->value,
            post: $post,
            reason: $reason
        );

        $this->em->flush();
    }

    public function deletePost(Post $post, User $moderator, ?string $reason = null): void
    {
        if ($post->getStatus() === PostStatus::DELETED) {
            return;
        }

        $previousStatus = $post->getStatus();

        $post->setStatus(PostStatus::DELETED);
        $post->setDeletedAt(new \DateTimeImmutable());

        $this->logAction(
            actionType: ModerationActionType::DELETED,
            moderator: $moderator,
            previousStatus: $previousStatus->value,
            newStatus: PostStatus::DELETED->value,
            post: $post,
            reason: $reason
        );

        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | COMMENT MODERATION
     |--------------------------------------------------------------------------
     */

    public function hideComment(Comment $comment, User $moderator, ?string $reason = null): void
    {
        if ($comment->getStatus() === CommentStatus::HIDDEN_BY_MODERATOR) {
            return;
        }

        $previousStatus = $comment->getStatus();

        $comment->setStatus(CommentStatus::HIDDEN_BY_MODERATOR);

        $this->logAction(
            actionType: ModerationActionType::MODERATOR_HIDDEN,
            moderator: $moderator,
            previousStatus: $previousStatus->value,
            newStatus: CommentStatus::HIDDEN_BY_MODERATOR->value,
            comment: $comment,
            reason: $reason
        );

        $this->em->flush();
    }

    public function restoreComment(Comment $comment, User $moderator, ?string $reason = null): void
    {
        if ($comment->getStatus() === CommentStatus::PUBLISHED) {
            return;
        }

        $previousStatus = $comment->getStatus();

        $comment->setStatus(CommentStatus::PUBLISHED);

        $this->logAction(
            actionType: ModerationActionType::RESTORED,
            moderator: $moderator,
            previousStatus: $previousStatus->value,
            newStatus: CommentStatus::PUBLISHED->value,
            comment: $comment,
            reason: $reason
        );

        $this->em->flush();
    }

    public function deleteComment(Comment $comment, User $moderator, ?string $reason = null): void
    {
        if ($comment->getStatus() === CommentStatus::DELETED) {
            return;
        }

        $previousStatus = $comment->getStatus();

        $comment->setStatus(CommentStatus::DELETED);
        $comment->setDeletedAt(new \DateTimeImmutable());

        $this->logAction(
            actionType: ModerationActionType::DELETED,
            moderator: $moderator,
            previousStatus: $previousStatus->value,
            newStatus: CommentStatus::DELETED->value,
            comment: $comment,
            reason: $reason
        );

        $this->em->flush();
    }

    /*
     |--------------------------------------------------------------------------
     | PRIVATE LOGGER
     |--------------------------------------------------------------------------
     */

    private function logAction(
        ModerationActionType $actionType,
        User $moderator,
        ?string $previousStatus = null,
        ?string $newStatus = null,
        ?Post $post = null,
        ?Comment $comment = null,
        ?string $reason = null
    ): void {
        $log = new ModerationActionLog();

        $log->setActionType($actionType)
            ->setModerator($moderator)
            ->setPreviousStatus($previousStatus)
            ->setNewStatus($newStatus)
            ->setReason($reason)
            ->setPost($post)
            ->setComment($comment);

        $this->em->persist($log);
    }
}