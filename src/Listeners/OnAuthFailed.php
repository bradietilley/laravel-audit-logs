<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Database\Eloquent\Model;

class OnAuthFailed extends AuditListener
{
    public const string ACTION = 'Login failed';

    public function handle(Failed $event): void
    {
        $user = $event->user;

        if (! is_null($user) && ! $user instanceof Model) {
            return;
        }

        $field = AuditLogConfig::getUserIdentifier();

        $this->recorder->record(self::ACTION, $user, AuditLog::TYPE_AUDIT, [
            'event' => [
                'guard' => $event->guard,
                $field => $event->credentials[$field] ?? $user?->getAttribute($field),
            ],
        ]);
    }
}
