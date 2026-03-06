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
        return \in_array($this, [
            self::AUTO_HIDDEN,
            self::HIDDEN_BY_MODERATOR,
        ], true);
    }

    public function isDeleted(): bool
    {
        return $this === self::DELETED;
    }

    /**
     * Contenu affecté par une modération (auto ou humaine).
     */
    public function isModerated(): bool
    {
        return $this !== self::PUBLISHED;
    }

    /**
     * Modération automatique (ex : seuil de signalements atteint).
     */
    public function isAutoModerated(): bool
    {
        return $this === self::AUTO_HIDDEN;
    }

    /**
     * Modération humaine.
     */
    public function isManuallyModerated(): bool
    {
        return \in_array($this, [
            self::HIDDEN_BY_MODERATOR,
            self::DELETED,
        ], true);
    }

    public function labelKey(): string
    {
        return match ($this) {
            self::PUBLISHED => 'post.status.published',
            self::AUTO_HIDDEN => 'post.status.auto_hidden',
            self::HIDDEN_BY_MODERATOR => 'post.status.hidden_by_moderator',
            self::DELETED => 'post.status.deleted',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $s) => $s->value, self::cases());
    }

    public static function choices(): array
    {
        $choices = [];

        foreach (self::cases() as $case) {
            $choices[$case->labelKey()] = $case->value;
        }

        return $choices;
    }
}