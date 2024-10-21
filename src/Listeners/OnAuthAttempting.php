<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\RequestGuard;
use Illuminate\Auth\SessionGuard;
use Illuminate\Auth\TokenGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OnAuthAttempting extends AuditListener
{
    public const ACTION = 'Login Attempt';

    public function handle(Attempting $event): void
    {
        $provider = null;

        try {
            $guard = Auth::guard($event->guard);

            if ($guard instanceof RequestGuard || $guard instanceof SessionGuard || $guard instanceof TokenGuard) {
                $provider = $guard->getProvider();
            }
        } catch (Throwable) {
            //
        }

        if ($provider === null) {
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
