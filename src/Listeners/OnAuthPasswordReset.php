<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Model;

class OnAuthPasswordReset extends AuditListener
{
    public const ACTION = 'Password reset successful';

    public function handle(PasswordReset $event): void
    {
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
