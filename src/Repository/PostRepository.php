<?php

namespace App\Repository;

use App\Entity\Post;
use App\Enum\PostStatus;
use App\Enum\VoteType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository principal des posts.
 *
 * Contient :
 * - Derniers posts
 * - Top du moment (algo mixte + récence)
 * - Légendes (impact durable humour)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * ===============================
     * Derniers posts publiés
     * ===============================
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * ===============================
     * 🔥 TOP DU MOMENT
     * ===============================
     *
     * Algorithme interne (invisible) :
     *
     * 1. Humour = Laugh + Disillusioned
     * 2. Colère = Angry
     * 3. Volume = total votes
     *
     * ScoreMoment =
     *      (Humour × 3)
     *    - (Colère × 1.5)
     *    + (Volume × 0.5)
     *
     * Puis déclin temporel :
     *
     * FinalScore = ScoreMoment / (heures + 6)^1.2
     *
     * Objectif :
     * - Favoriser humour / ironie
     * - Limiter la domination de la colère
     * - Booster la fraîcheur
     */
    public function findTopDuMoment(int $limit = 10): array
    {
        $posts = $this->createQueryBuilder('p')
            ->leftJoin('p.votes', 'v')
            ->addSelect('v')
            ->where('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->getQuery()
            ->getResult();

        $now = new \DateTimeImmutable();
        $scored = [];

        foreach ($posts as $post) {

            $laugh = 0;
            $disillusioned = 0;
            $angry = 0;

            foreach ($post->getVotes() as $vote) {
                match ($vote->getType()) {
                    VoteType::LAUGH => $laugh++,
                    VoteType::DISILLUSIONED => $disillusioned++,
                    VoteType::ANGRY => $angry++,
                };
            }

            $humour = $laugh + $disillusioned;
            $volume = $laugh + $disillusioned + $angry;

            $scoreMoment =
                ($humour * 3)
                - ($angry * 1.5)
                + ($volume * 0.5);

            // Calcul ancienneté en heures
            $interval = $post->getCreatedAt()->diff($now);
            $hours = ($interval->days * 24) + $interval->h;

            $finalScore = $scoreMoment / pow(($hours + 6), 1.2);

            $scored[] = [
                'post' => $post,
                'score' => $finalScore,
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(
            array_map(fn($row) => $row['post'], $scored),
            0,
            $limit
        );
    }

    /**
     * ===============================
     * 🏛 LÉGENDES
     * ===============================
     *
     * Classement durable.
     * Pas de déclin temporel.
     *
     * Score = (Laugh + Disillusioned) × 2
     *
     * Objectif :
     * - Mettre en avant les posts cultes
     * - Construire la mémoire du site
     */
    public function findLegendes(int $limit = 10): array
    {
        $posts = $this->createQueryBuilder('p')
            ->leftJoin('p.votes', 'v')
            ->addSelect('v')
            ->where('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->getQuery()
            ->getResult();

        $scored = [];

        foreach ($posts as $post) {

            $laugh = 0;
            $disillusioned = 0;

            foreach ($post->getVotes() as $vote) {
                match ($vote->getType()) {
                    VoteType::LAUGH => $laugh++,
                    VoteType::DISILLUSIONED => $disillusioned++,
                    default => null,
                };
            }

            $score = ($laugh + $disillusioned) * 2;

            $scored[] = [
                'post' => $post,
                'score' => $score,
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(
            array_map(fn($row) => $row['post'], $scored),
            0,
            $limit
        );
    }
}