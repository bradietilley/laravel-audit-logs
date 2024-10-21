<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Database\Eloquent\Model;

class OnAuthOtherDeviceLogout extends AuditListener
{
    public const ACTION = 'Logout (other device) successful';

    public function handle(OtherDeviceLogout $event): void
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
