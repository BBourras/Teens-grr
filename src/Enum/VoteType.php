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

    public function labelKey(): string
    {
        return match ($this) {
            self::LIKE => 'vote.like',
            self::LAUGH => 'vote.laugh',
            self::ANGRY => 'vote.angry',
        };
    }

    /**
     * Poids utilisé pour calculer le score de popularité.
     */
    public function weight(): int
    {
        return match ($this) {
            self::LIKE => 1,
            self::LAUGH => 2,
            self::ANGRY => 1,
        };
    }

    /**
     * Libellé prêt pour affichage UI.
     */
    public function displayLabel(): string
    {
        return $this->emoji() . ' ' . $this->labelKey();
    }

    public static function values(): array
    {
        return array_map(static fn (self $t) => $t->value, self::cases());
    }

    /**
     * Pour ChoiceType Symfony.
     */
    public static function choices(): array
    {
        $choices = [];

        foreach (self::cases() as $case) {
            $choices[$case->displayLabel()] = $case->value;
        }

        return $choices;
    }
}