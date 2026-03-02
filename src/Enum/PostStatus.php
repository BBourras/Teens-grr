<?php

namespace App\Enum;

enum PostStatus: string
{
    // Post visible par tous
    case PUBLISHED = 'published';

    // Masquage automatique après plusieurs signalements
    case AUTO_HIDDEN = 'auto_hidden';

    // Masquage manuel par un modérateur ou admin
    case HIDDEN_BY_MODERATOR = 'hidden_by_moderator';

    // Suppression définitive
    case DELETED = 'deleted';

    /**
     * Vérifie si le post est visible sur le site
     */
    public function isVisible(): bool
    {
        return match($this) {
            self::PUBLISHED => true,
            self::AUTO_HIDDEN,
            self::HIDDEN_BY_MODERATOR,
            self::DELETED => false,
        };
    }

    /**
     * Retourne tous les objets Enum
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Retourne toutes les valeurs string
     */
    public static function allValues(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}