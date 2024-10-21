<?php

use BradieTilley\AuditLogs\Models\AuditLog;

return [
    'models' => [
        /**
         * The audit log model to use
         *
         * @var class-string<AuditLog>
         */
        'audit_log' => AuditLog::class,
    ],

    /**
     * The log channel to write to (if specified)
     *
     * @var ?string
     */
    'log_channel' => 'audit_logs',

    /**
     * The attribute to include in all default authentication logs such as login,
     * password reset, etc.
     *
     * @var string
     */
    'user_identifier' => 'email',
];
