<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Vote;
use App\Enum\PostStatus;
use App\Enum\VoteType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ======================================================
        // 1️⃣ USERS (Admin + Mod + 10 users)
        // ======================================================

        $users = [];

        // Admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setUsername('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);
        $users[] = $admin;

        // Modérateur
        $mod = new User();
        $mod->setEmail('mod@test.com');
        $mod->setUsername('Moderator');
        $mod->setRoles(['ROLE_MODERATOR']);
        $mod->setPassword(
            $this->passwordHasher->hashPassword($mod, 'mod123')
        );
        $manager->persist($mod);
        $users[] = $mod;

        // Users classiques
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user$i@test.com");
            $user->setUsername($faker->userName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );

            $manager->persist($user);
            $users[] = $user;
        }

        // ======================================================
        // 2️⃣ POSTS (50 posts)
        // ======================================================

        $posts = [];

        for ($i = 0; $i < 50; $i++) {

            $post = new Post();
            $post->setTitle($faker->sentence(6));
            $post->setContent($faker->paragraphs(3, true));
            $post->setAuthor($users[array_rand($users)]);

            // Statuts variés
            $roll = rand(1, 100);

            if ($roll <= 65) {
                // 65% des posts visibles
                $post->setStatus(PostStatus::PUBLISHED);
            } elseif ($roll <= 80) {
                // 15% auto modérés (ex : trop de signalements)
                $post->setStatus(PostStatus::AUTO_HIDDEN);
            } elseif ($roll <= 95) {
                // 15% masqués manuellement
                $post->setStatus(PostStatus::HIDDEN_BY_MODERATOR);
            } else {
                // 5% supprimés
                $post->setStatus(PostStatus::DELETED);
            }

            $manager->persist($post);
            $posts[] = $post;
        }

        // ======================================================
        // 3️⃣ COMMENTAIRES (0 à 5 par post)
        // ======================================================

        foreach ($posts as $post) {

            $commentCount = rand(0, 5);

            for ($i = 0; $i < $commentCount; $i++) {

                $comment = new Comment();
                $comment->setContent($faker->sentence(12));
                $comment->setAuthor($users[array_rand($users)]);
                $comment->setPost($post);

                $post->incrementCommentCount();

                $manager->persist($comment);
            }
        }

        // ======================================================
        // 4️⃣ VOTES déséquilibrés (Laugh dominant)
        // ======================================================

        foreach ($posts as $post) {

            foreach ($users as $user) {

                if (rand(1, 100) <= 70) { // 70% des users votent

                    $roll = rand(1, 100);

                    if ($roll <= 60) {
                        $voteType = VoteType::LAUGH;           // dominant
                    } elseif ($roll <= 85) {
                        $voteType = VoteType::DISILLUSIONED;   // second
                    } else {
                        $voteType = VoteType::ANGRY;           // minoritaire
                    }

                    $vote = new Vote();
                    $vote->setPost($post);
                    $vote->setUser($user);
                    $vote->setType($voteType);

                    // Score pondéré mis à jour
                    $post->incrementReactionScore($voteType->weight());

                    $manager->persist($vote);
                }
            }
        }

        $manager->flush();
    }
}
