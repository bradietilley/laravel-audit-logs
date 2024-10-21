<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;

class OnAuthVerified extends AuditListener
{
    public const ACTION = 'Email verification successful';

    public function handle(Verified $event): void
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
