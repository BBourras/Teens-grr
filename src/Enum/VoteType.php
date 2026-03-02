<?php

namespace App\Enum;

enum VoteType: string
{
    case LIKE = 'like';
    case LAUGH = 'laugh';
    case ANGRY = 'angry';

    /**
     * Retourne l’emoji associé
     */
    public function emoji(): string
    {
        return match($this) {
            self::LIKE => '👍',
            self::LAUGH => '😂',
            self::ANGRY => '😡',
        };
    }

    /**
     * Retourne le label lisible
     */
    public function label(): string
    {
        return match($this) {
            self::LIKE => 'Like',
            self::LAUGH => 'Laugh',
            self::ANGRY => 'Angry',
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
     * Utile pour les comparaisons ou clés de tableau
     */
    public static function allValues(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}