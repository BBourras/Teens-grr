<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Report;
use App\Entity\ModerationActionLog;
use App\Enum\PostStatus;
use App\Enum\CommentStatus;
use App\Enum\ModerationActionType;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReportService
{
    private const AUTO_HIDE_THRESHOLD = 5;

    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepository
    ) {}

    public function reportPost(Post $post, User $user, ?string $reason = null): void
    {
        if ($this->reportRepository->findOneBy(['post' => $post, 'user' => $user])) {
            return;
        }

        $report = (new Report())
            ->setPost($post)
            ->setUser($user)
            ->setReason($reason);

        $post->incrementReportCount();
        $this->em->persist($report);

        if ($post->getReportCount() >= self::AUTO_HIDE_THRESHOLD
            && $post->getStatus() === PostStatus::PUBLISHED) {

            $previousStatus = $post->getStatus();
            $post->setStatus(PostStatus::AUTO_HIDDEN);

            $log = new ModerationActionLog();
            $log->setActionType(ModerationActionType::AUTO_HIDE)
                ->setPreviousStatus($previousStatus->value)
                ->setNewStatus(PostStatus::AUTO_HIDDEN->value)
                ->setModerator($user)
                ->setPost($post);

            $this->em->persist($log);
        }

        $this->em->flush();
    }

    public function reportComment(Comment $comment, User $user, ?string $reason = null): void
    {
        if ($this->reportRepository->findOneBy(['comment' => $comment, 'user' => $user])) {
            return;
        }

        $report = (new Report())
            ->setComment($comment)
            ->setUser($user)
            ->setReason($reason);

        $comment->incrementReportCount();
        $this->em->persist($report);

        if ($comment->getReportCount() >= self::AUTO_HIDE_THRESHOLD
            && $comment->getStatus() === CommentStatus::PUBLISHED) {

            $previousStatus = $comment->getStatus();
            $comment->setStatus(CommentStatus::AUTO_HIDDEN);

            $log = new ModerationActionLog();
            $log->setActionType(ModerationActionType::AUTO_HIDE)
                ->setPreviousStatus($previousStatus->value)
                ->setNewStatus(CommentStatus::AUTO_HIDDEN->value)
                ->setModerator($user)
                ->setComment($comment);

            $this->em->persist($log);
        }

        $this->em->flush();
    }
}