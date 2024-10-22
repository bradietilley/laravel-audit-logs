<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLogs\Concerns\HasAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use BradieTilley\AuditLogs\Loggers\ModelLogger;
use Workbench\App\Models\Loggers\StaffLogger;

class Staff extends User implements WithAuditLogs
{
    use HasAuditLogs;

    protected $guarded = [];

    public function getAuditLogger(): ModelLogger
    {
        return new StaffLogger($this);
    }
}
