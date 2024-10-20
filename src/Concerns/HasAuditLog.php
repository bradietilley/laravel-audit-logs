<?php

namespace BradieTilley\AuditLog\Concerns;

use BradieTilley\AuditLog\AuditLogConfig;
use BradieTilley\AuditLog\Contracts\WithAuditLog;
use BradieTilley\AuditLog\Loggers\ModelLogger;
use BradieTilley\AuditLog\Observers\HasAuditLogObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 * @mixin WithAuditLog
 */
trait HasAuditLog
{
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
