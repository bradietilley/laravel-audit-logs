<?php

namespace BradieTilley\AuditLogs\Contracts;

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface WithPerformedAuditLogs
{
    /**
     * @return MorphMany<AuditLog, Model>
     */
    public function performedAuditLogs(): MorphMany;
}
