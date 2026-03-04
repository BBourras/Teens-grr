<?php

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
        return in_array($this, [
            self::AUTO_HIDDEN,
            self::HIDDEN_BY_MODERATOR,
        ], true);
    }

    public function isDeleted(): bool
    {
        return $this === self::DELETED;
    }

    public static function all(): array
    {
        return self::cases();
    }

    public static function allValues(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}