<?php

namespace BradieTilley\AuditLogs\Observers;

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Support\Str;

class AuditLogObserver
{
    public function creating(AuditLog $auditLog): void
    {
        $auditLog->fill([
            'ulid' => $auditLog->ulid ?? (string) Str::ulid(),
        ]);
    }
}
