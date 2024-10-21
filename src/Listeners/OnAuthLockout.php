<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Lockout;

class OnAuthLockout extends AuditListener
{
    public const ACTION = 'Request locked out';

    public function handle(Lockout $event): void
    {
        $this->recorder->record(null, static::ACTION, AuditLog::TYPE_AUDIT, [
        ]);
    }
}
