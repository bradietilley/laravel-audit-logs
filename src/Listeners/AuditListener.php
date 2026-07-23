<?php

namespace BradieTilley\AuditLogs\Listeners;

use BradieTilley\AuditLogs\Contracts\AuditLogger;

abstract class AuditListener
{
    public function __construct(public readonly AuditLogger $recorder)
    {
    }
}
