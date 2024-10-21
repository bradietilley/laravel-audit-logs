<?php

use App\Models\User;
use BradieTilley\AuditLogs\Models\AuditLog;

return [
    'models' => [
        'user' => User::class,
        'audit_log' => AuditLog::class,
    ],

    'log_channel' => 'audit_logs',

    /**
     * The attribute to include in all default authentication logs such as login,
     * password reset, etc.
     */
    'user_identifier' => 'email',
];
