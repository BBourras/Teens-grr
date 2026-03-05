<?php

declare(strict_types=1);

namespace App\Enum;

enum ModerationActionType: string
{
    /**
     * Un signalement a été créé (optionnel, utile si tu veux auditer finement).
     */
    case REPORT_CREATED = 'report_created';

    /**
     * Masquage automatique (ex: seuil 5 reports atteint).
     */
    case AUTO_HIDE = 'auto_hide';

    /**
     * Masquage manuel par modérateur/admin.
     */
    case MODERATOR_HIDE = 'moderator_hide';

    /**
     * Restauration (re-publication) après masquage.
     */
    case RESTORE = 'restore';

    /**
     * Suppression initiée par l'auteur (suppression de son propre contenu).
     */
    case AUTHOR_DELETE = 'author_delete';

    /**
     * Suppression par modérateur/admin.
     */
    case MODERATOR_DELETE = 'moderator_delete';

    /**
     * Décision modération : les signalements sont confirmés (le contenu reste caché / sanctionné).
     */
    case REPORTS_CONFIRMED = 'reports_confirmed';

    /**
     * Décision modération : les signalements sont infirmés (le contenu peut être restauré).
     */
    case REPORTS_DISMISSED = 'reports_dismissed';

    public function labelKey(): string
    {
        return match ($this) {
            self::REPORT_CREATED => 'moderation.action.report_created',
            self::AUTO_HIDE => 'moderation.action.auto_hide',
            self::MODERATOR_HIDE => 'moderation.action.moderator_hide',
            self::RESTORE => 'moderation.action.restore',
            self::AUTHOR_DELETE => 'moderation.action.author_delete',
            self::MODERATOR_DELETE => 'moderation.action.moderator_delete',
            self::REPORTS_CONFIRMED => 'moderation.action.reports_confirmed',
            self::REPORTS_DISMISSED => 'moderation.action.reports_dismissed',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $a) => $a->value, self::cases());
    }
}
