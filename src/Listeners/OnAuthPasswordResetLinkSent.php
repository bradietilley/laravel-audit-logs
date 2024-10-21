<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Database\Eloquent\Model;

class OnAuthPasswordResetLinkSent extends AuditListener
{
    public const ACTION = 'Password reset link sent';

    /** @phpstan-ignore-next-line */
    public function handle(PasswordResetLinkSent $event): void
    {
        /** @phpstan-ignore-next-line */
        $user = $event->user;

        if (! $user instanceof Model) {
            return;
        }

        $field = AuditLogConfig::getUserIdentifier();

        $this->recorder->record($user, static::ACTION, AuditLog::TYPE_AUDIT, [
            'event' => [
                $field => $user->getAttribute($field),
            ],
        ]);
    }
}
