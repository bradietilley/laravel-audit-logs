<?php

namespace BradieTilley\AuditLogs\Concerns;

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin Model
 * @mixin WithActionLogs
 */
trait HasPerformedAuditLogs
{
    /**
     * @return HasMany<AuditLog, static>
     */
    public function performedAuditLogs(): HasMany
    {
        return $this->hasMany(
            AuditLogConfig::getAuditLogModel(),
            'user_id',
        )->orderByDesc('id');
    }
}
