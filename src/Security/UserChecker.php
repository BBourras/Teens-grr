<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * Vérification avant l’authentification (login)
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Exemple : bloquer les utilisateurs désactivés
        if (in_array('ROLE_BANNED', $user->getRoles(), true)) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été suspendu. Contactez un administrateur.'
            );
        }

        // Si besoin, on peut ajouter d'autres vérifications :
        // - Email non confirmé
        // - Compte expiré
    }

    /**
     * Vérification après authentification (après mot de passe)
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Exemple : forcer un changement de mot de passe
        // if ($user->isPasswordExpired()) { ... }
    }
}