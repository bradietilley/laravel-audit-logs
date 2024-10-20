<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLogs\Concerns\HasAuditLog;
use BradieTilley\AuditLogs\Contracts\WithAuditLog;
use BradieTilley\AuditLogs\Loggers\ModelLogger;
use Workbench\App\Models\Loggers\StaffLogger;

class Staff extends User implements WithAuditLog
{
    use HasAuditLog;

    protected $guarded = [];

    public function getAuditLogger(): ModelLogger
    {
        return new StaffLogger($this);
    }
}
