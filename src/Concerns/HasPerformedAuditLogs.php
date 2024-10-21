<?php

namespace BradieTilley\AuditLogs\Concerns;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 * @mixin WithActionLogs
 */
trait HasPerformedAuditLogs
{
    /**
     * @return MorphMany<AuditLog, static>
     */
    public function performedAuditLogs(): MorphMany
    {
        return $this->morphMany(
            AuditLogConfig::getAuditLogModel(),
            'user',
            'user_type',
            'user_id',
        )->orderByDesc('id');
    }
}
