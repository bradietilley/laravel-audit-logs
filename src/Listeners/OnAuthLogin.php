<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;

class OnAuthLogin extends AuditListener
{
    public const ACTION = 'Login successful';

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof Model) {
            return;
        }

        $field = AuditLogConfig::getUserIdentifier();

        $this->recorder->record($user, static::ACTION, AuditLog::TYPE_AUDIT, [
            'event' => [
                'guard' => $event->guard,
                'remember' => $event->remember,
                $field => $user->getAttribute($field),
            ],
        ]);
    }
}
