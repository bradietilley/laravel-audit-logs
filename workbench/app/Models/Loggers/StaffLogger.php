<?php

namespace Workbench\App\Models\Loggers;

use BradieTilley\AuditLog\Loggers\ModelLogger;

class StaffLogger extends ModelLogger
{
    public function updated(): void
    {
        $this->recordSingleLog('StaffLogger custom log');
    }
}
