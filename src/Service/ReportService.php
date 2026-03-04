<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Report;
use App\Entity\User;
use App\Entity\ModerationActionLog;
use App\Enum\PostStatus;
use App\Enum\CommentStatus;
use App\Enum\ModerationActionType;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReportService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepository,
    ) {}

    public function reportPost(Post $post, User $user, ?string $reason = null): void
    {
        // Vérifie si déjà signalé
        if ($this->reportRepository->findOneBy([
            'post' => $post,
            'user' => $user
        ])) {
            return;
        }

        $report = new Report();
        $report->setPost($post)
               ->setUser($user)
               ->setReason($reason);

        $post->incrementReportCount();

        $this->em->persist($report);

        // Auto-masquage à 5 signalements
        if ($post->getReportCount() >= 5 && $post->getStatus() === PostStatus::PUBLISHED) {

            $previousStatus = $post->getStatus();

            $post->setStatus(PostStatus::AUTO_HIDDEN);

            $log = new ModerationActionLog();
            $log->setActionType(ModerationActionType::AUTO_HIDDEN)
                ->setPreviousStatus($previousStatus->value)
                ->setNewStatus(PostStatus::AUTO_HIDDEN->value)
                ->setModerator($user) // auteur du 5e signalement
                ->setPost($post);

            $this->em->persist($log);
        }

        $this->em->flush();
    }

    public function reportComment(Comment $comment, User $user, ?string $reason = null): void
    {
        if ($this->reportRepository->findOneBy([
            'comment' => $comment,
            'user' => $user
        ])) {
            return;
        }

        $report = new Report();
        $report->setComment($comment)
               ->setUser($user)
               ->setReason($reason);

        $comment->incrementReportCount();

        $this->em->persist($report);

        if ($comment->getReportCount() >= 5 && $comment->getStatus() === CommentStatus::PUBLISHED) {

            $previousStatus = $comment->getStatus();

            $comment->setStatus(CommentStatus::AUTO_HIDDEN);

            $log = new ModerationActionLog();
            $log->setActionType(ModerationActionType::AUTO_HIDDEN)
                ->setPreviousStatus($previousStatus->value)
                ->setNewStatus(CommentStatus::AUTO_HIDDEN->value)
                ->setModerator($user)
                ->setComment($comment);

            $this->em->persist($log);
        }

        $this->em->flush();
    }
}