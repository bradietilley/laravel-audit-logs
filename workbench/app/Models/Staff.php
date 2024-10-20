<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLog\Concerns\HasAuditLog;
use BradieTilley\AuditLog\Contracts\WithAuditLog;
use BradieTilley\AuditLog\Loggers\ModelLogger;
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
