<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OnAuthAttempting extends AuditListener
{
    public const ACTION = 'Login Attempt';

    public function handle(Attempting $event): void
    {
        try {
            $provider = Auth::guard($event->guard)->getProvider(); /** @phpstan-ignore-line */
        } catch (Throwable) {
            return;
        }

        $user = $provider->retrieveByCredentials($event->credentials);

        if (! is_null($user) && ! $user instanceof Model) {
            return;
        }

        $field = AuditLogConfig::getUserIdentifier();

        $this->recorder->record($user, static::ACTION, AuditLog::TYPE_AUDIT, [
            'event' => [
                'guard' => $event->guard,
                'remember' => $event->remember,
                $field => $event->credentials[$field] ?? null,
            ],
        ]);
    }
}
