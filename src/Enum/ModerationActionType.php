<?php

namespace App\Enum;

enum ModerationActionType: string
{
    // Masquage automatique après 5 signalements d'utilisateurs
    case AUTO_HIDDEN = 'auto_hidden';

    // Masquage manuel par un modérateur ou admin
    case MODERATOR_HIDDEN = 'moderator_hidden';

    // Restauration d’un post/commentaire précédemment masqué
    case RESTORED = 'restored';

    // Suppression définitive d’un post/commentaire
    case DELETED = 'deleted';
}