<?php

use App\Models\User;
use BradieTilley\AuditLogs\Models\AuditLog;

return [
    'models' => [
        'user' => User::class,
        'audit_log' => AuditLog::class,
        'audit_identity' => AuditIdentity::class,
    ],

    'log_channel' => 'audit_logs',
];
