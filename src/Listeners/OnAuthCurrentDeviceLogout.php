<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Database\Eloquent\Model;

class OnAuthCurrentDeviceLogout extends AuditListener
{
    public const ACTION = 'Logout (current device) successful';

    public function handle(CurrentDeviceLogout $event): void
    {
        $user = $event->user;

        if (! $user instanceof Model) {
            return;
        }

        $field = AuditLogConfig::getUserIdentifier();

        $this->recorder->record($user, static::ACTION, AuditLog::TYPE_AUDIT, [
            'event' => [
                'guard' => $event->guard,
                $field => $user->getAttribute($field),
            ],
        ]);
    }
}
