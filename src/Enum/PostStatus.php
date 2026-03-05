<?php

declare(strict_types=1);

namespace App\Enum;

enum PostStatus: string
{
    case PUBLISHED = 'published';
    case AUTO_HIDDEN = 'auto_hidden';
    case HIDDEN_BY_MODERATOR = 'hidden_by_moderator';
    case DELETED = 'deleted';

    public function isVisible(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function isHidden(): bool
    {
        return \in_array($this, [self::AUTO_HIDDEN, self::HIDDEN_BY_MODERATOR], true);
    }

    public function isDeleted(): bool
    {
        return $this === self::DELETED;
    }

    /**
     * Clé de traduction (recommandé) : post.status.published, etc.
     */
    public function labelKey(): string
    {
        return match ($this) {
            self::PUBLISHED => 'post.status.published',
            self::AUTO_HIDDEN => 'post.status.auto_hidden',
            self::HIDDEN_BY_MODERATOR => 'post.status.hidden_by_moderator',
            self::DELETED => 'post.status.deleted',
        };
    }

    /**
     * Valeurs string de l'enum (utile pour validation / contraintes).
     */
    public static function values(): array
    {
        return array_map(static fn (self $s) => $s->value, self::cases());
    }

    /**
     * Helper pour ChoiceType (Symfony Forms).
     * Retourne: ['post.status.published' => 'published', ...]
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->labelKey()] = $case->value;
        }

        return $choices;
    }
}
