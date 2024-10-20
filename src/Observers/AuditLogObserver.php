<?php

namespace BradieTilley\AuditLog\Observers;

use BradieTilley\AuditLog\Models\AuditLog;
use Illuminate\Support\Str;

class AuditLogObserver
{
    public function creating(AuditLog $auditLog): void
    {
        $auditLog->fill([
            'ulid' => $auditLog->ulid ?? Str::ulid()->toString(),
        ]);
    }
}
