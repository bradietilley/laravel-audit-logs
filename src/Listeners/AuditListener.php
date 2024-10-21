<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\AuditLogRecorder;

abstract class AuditListener
{
    public function __construct(public readonly AuditLogRecorder $recorder)
    {
    }
}
