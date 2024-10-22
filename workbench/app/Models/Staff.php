<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLogs\Concerns\HasAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;

class Staff extends User implements WithAuditLogs
{
    use HasAuditLogs;

    protected $guarded = [];
}
