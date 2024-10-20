<?php

namespace BradieTilley\AuditLog\Contracts;

use BradieTilley\AuditLog\Loggers\ModelLogger;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface WithAuditLog
{
    public function auditLogs(): MorphMany;

    public function getAuditLogger(): ModelLogger;
}
