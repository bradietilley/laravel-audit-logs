<?php

namespace BradieTilley\AuditLogs\Concerns;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use BradieTilley\AuditLogs\Loggers\ModelLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use BradieTilley\AuditLogs\Observers\HasAuditLogsObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 * @mixin WithAuditLogs
 */
trait HasAuditLogs
{
    /**
     * Get audit logs relating to this resource.
     *
     * @return MorphMany<AuditLog, static>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(
            AuditLogConfig::getAuditLogModel(),
            'model',
            'model_type',
            'model_id',
        )->orderByDesc('id');
    }

    /**
     * Get audit logs that were actioned by this user/resource.
     *
     * @return MorphMany<AuditLog, static>
     */
    public function actionLogs(): MorphMany
    {
        return $this->morphMany(
            AuditLogConfig::getAuditLogModel(),
            'user',
            'user_type',
            'user_id',
        )->orderByDesc('id');
    }

    public static function bootHasAuditLogs(): void
    {
        self::observe(HasAuditLogsObserver::class);
    }

    public function getAuditLogger(): ModelLogger
    {
        return new ModelLogger($this);
    }
}
