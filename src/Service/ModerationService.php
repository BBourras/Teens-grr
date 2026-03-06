<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\ModerationActionLog;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\CommentStatus;
use App\Enum\ModerationActionType;
use App\Enum\PostStatus;
use Doctrine\ORM\EntityManagerInterface;

class ModerationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function hideByModerator(Post|Comment $entity, User $moderator, ?string $reason = null): void
    {
        $previousStatus = $this->getStatusString($entity);

        if ($entity instanceof Post) {
            $entity->setStatus(PostStatus::HIDDEN_BY_MODERATOR);
        } elseif ($entity instanceof Comment) {
            $entity->setStatus(CommentStatus::HIDDEN_BY_MODERATOR);
        }

        $this->logAction($entity, $moderator, ModerationActionType::MODERATOR_HIDE, $previousStatus, $this->getStatusString($entity), $reason);
        $this->em->flush();
    }

    public function restore(Post|Comment $entity, User $moderator, ?string $reason = null): void
    {
        $previousStatus = $this->getStatusString($entity);

        if ($entity instanceof Post) {
            $entity->setStatus(PostStatus::PUBLISHED);
        } elseif ($entity instanceof Comment) {
            $entity->setStatus(CommentStatus::PUBLISHED);
        }

        $this->logAction($entity, $moderator, ModerationActionType::RESTORE, $previousStatus, $this->getStatusString($entity), $reason);
        $this->em->flush();
    }

    public function delete(Post|Comment $entity, User $moderator, ?string $reason = null): void
    {
        $previousStatus = $this->getStatusString($entity);

        if ($entity instanceof Post) {
            $entity->setStatus(PostStatus::DELETED);
        } elseif ($entity instanceof Comment) {
            $entity->setStatus(CommentStatus::DELETED);
        }

        $this->logAction($entity, $moderator, ModerationActionType::MODERATOR_DELETE, $previousStatus, $this->getStatusString($entity), $reason);
        $this->em->flush();
    }

    private function logAction(
        Post|Comment $entity,
        User $moderator,
        ModerationActionType $actionType,
        string $previousStatus,
        string $newStatus,
        ?string $reason = null
    ): void {
        $log = new ModerationActionLog();
        $log->setModerator($moderator)
            ->setActionType($actionType)
            ->setPreviousStatus($previousStatus)
            ->setNewStatus($newStatus)
            ->setReason($reason);

        if ($entity instanceof Post) {
            $log->setPost($entity);
        } else {
            $log->setComment($entity);
        }

        $log->assertExactlyOneTarget();
        $this->em->persist($log);
    }

    private function getStatusString(Post|Comment $entity): string
    {
        return $entity->getStatus()->value;
    }
}