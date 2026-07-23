<?php

namespace BradieTilley\AuditLogs\Events;

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditLogRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AuditLog $auditLog)
    {
    }
}
