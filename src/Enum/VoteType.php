<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Représente les types de vote possibles.
 */
enum VoteType: string
{
    case LAUGH = 'laugh';
    case ANGRY = 'angry';
    case DISILLUSIONED = 'disillusioned';

    /**
     * Emoji associé pour l'affichage UI.
     */
    public function emoji(): string
    {
        return match ($this) {
            self::LAUGH => '😂',
            self::ANGRY => '😡',
            self::DISILLUSIONED => '😏', // moue ironique
        };
    }

    /**
     * Clé de traduction (pour i18n si besoin)
     */
    public function labelKey(): string
    {
        return match ($this) {
            self::LAUGH => 'vote.laugh',
            self::ANGRY => 'vote.angry',
            self::DISILLUSIONED => 'vote.disillusioned',
        };
    }

    /**
     * Poids pour le score global.
     */
    public function weight(): int
    {
        return match ($this) {
            self::LAUGH => 2,
            self::DISILLUSIONED => 2,
            self::ANGRY => 1,
        };
    }

    /**
     * Libellé prêt pour affichage UI.
     */
    public function displayLabel(): string
    {
        return $this->emoji() . ' ' . ucfirst($this->value);
    }

    /**
     * Retourne toutes les valeurs string (validation).
     */
    public static function values(): array
    {
        return array_map(static fn(self $t) => $t->value, self::cases());
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