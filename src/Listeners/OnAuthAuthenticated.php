<?php

namespace BradieTilley\AuditLogs\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Foundation\Auth\User;

class OnAuthAuthenticated extends AuditListener
{
    public function handle(Authenticated $event): void
    {
        /**
         * Preload the user model for subsequent logs to ensure that
         * we have access to the authorised user when handling the
         * logout event.
         */
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->recorder->setUser($user);
    }
}
