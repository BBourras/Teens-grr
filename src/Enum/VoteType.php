<?php

declare(strict_types=1);

namespace App\Enum;

enum VoteType: string
{
    case LIKE = 'like';
    case LAUGH = 'laugh';
    case ANGRY = 'angry';

    public function emoji(): string
    {
        return match ($this) {
            self::LIKE => '👍',
            self::LAUGH => '😂',
            self::ANGRY => '😡',
        };
    }

    /**
     * Clé de traduction (recommandé) : vote.like, vote.laugh, vote.angry
     */
    public function labelKey(): string
    {
        return match ($this) {
            self::LIKE => 'vote.like',
            self::LAUGH => 'vote.laugh',
            self::ANGRY => 'vote.angry',
        };
    }

    /**
     * Poids de la réaction pour calculer la popularité.
     * Ajuste selon ton produit.
     */
    public function weight(): int
    {
        return match ($this) {
            self::LIKE => 1,
            self::LAUGH => 2,
            self::ANGRY => 1,
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $t) => $t->value, self::cases());
    }

    /**
     * Pour ChoiceType:
     * Retourne: ['😂 vote.laugh' => 'laugh', ...]
     * (Tu peux ensuite traduire les clés avec Symfony Translator.)
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->emoji() . ' ' . $case->labelKey()] = $case->value;
        }

        return $choices;
    }
}
