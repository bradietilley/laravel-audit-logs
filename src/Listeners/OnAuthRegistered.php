<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;

class OnAuthRegistered extends AuditListener
{
    public const ACTION = 'User registered';

    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user instanceof Model) {
            return;
        }

        $field = AuditLogConfig::getUserIdentifier();

        $this->recorder->record($user, static::ACTION, AuditLog::TYPE_AUDIT, [
            'event' => [
                $field => $event->credentials[$field] ?? $user->getAttribute($field),
            ],
        ]);
    }
}
