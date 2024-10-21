<?php

namespace BradieTilley\AuditLogs\Contracts;

use BradieTilley\AuditLogs\Loggers\ModelLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface WithAuditLog
{
    /**
     * Get audit logs relating to this resource.
     *
     * @return MorphMany<AuditLog, Model>
     */
    public function auditLogs(): MorphMany;

    /**
     * Get audit logs that were actioned by this user/resource.
     *
     * @return MorphMany<AuditLog, Model>
     */
    public function actionLogs(): MorphMany;

    public function getAuditLogger(): ModelLogger;
}
