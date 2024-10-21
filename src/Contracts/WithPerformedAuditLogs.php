<?php

namespace BradieTilley\AuditLogs\Contracts;

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface WithPerformedAuditLogs
{
    /**
     * @return HasMany<AuditLog, Model>
     */
    public function performedAuditLogs(): HasMany;
}
