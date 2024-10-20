<?php

namespace BradieTilley\AuditLogs\Contracts;

use BradieTilley\AuditLogs\Loggers\ModelLogger;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface WithAuditLog
{
    public function auditLogs(): MorphMany;

    public function getAuditLogger(): ModelLogger;
}
