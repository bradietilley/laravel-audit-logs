<?php

namespace BradieTilley\AuditLogs\Contracts;

use BradieTilley\AuditLogs\Loggers\ModelLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface WithAuditLog
{
    /**
     * @return MorphMany<AuditLog, Model>
     */
    public function auditLogs(): MorphMany;

    public function getAuditLogger(): ModelLogger;
}
