<?php

namespace App\Repository;

use App\Entity\ModerationActionLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ModerationActionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModerationActionLog::class);
    }

    // méthodes custom pour filtrer logs (ex: par post/comment/mode)
}