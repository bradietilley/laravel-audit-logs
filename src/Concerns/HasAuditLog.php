<?php

namespace BradieTilley\AuditLogs\Concerns;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Contracts\WithAuditLog;
use BradieTilley\AuditLogs\Loggers\ModelLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use BradieTilley\AuditLogs\Observers\HasAuditLogObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 * @mixin WithAuditLog
 */
trait HasAuditLog
{
    /**
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

    public static function bootHasAuditLog(): void
    {
        self::observe(HasAuditLogObserver::class);
    }

    public function getAuditLogger(): ModelLogger
    {
        return new ModelLogger($this);
    }
}
