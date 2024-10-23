<?php

use BradieTilley\AuditLogs\Models\AuditLog;
use BradieTilley\AuditLogs\Observers\HasAuditLogsObserver;

return [
    'models' => [
        /**
         * The audit log model to use
         *
         * @var class-string<\BradieTilley\AuditLogs\Models\AuditLog>
         */
        'audit_log' => AuditLog::class,
    ],

    'classes' => [
        /**
         * The audit log observer class to use
         *
         * @var class-string<\BradieTilley\AuditLogs\Observers\HasAuditLogsObserver>
         */
        'observer' => HasAuditLogsObserver::class,
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

    /**
     * Configuration for when recording changes to a resource
     */
    'changes' => [
        /**
         * Truncate all strings to this length
         */
        'truncate_string_length' => 100,

        'ignored_fields' => [
            '*' => [
                'id',
                'updated_at',
                'deleted_at',
            ],

            'App\Models\User' => [
                'remember_token',
            ],
        ],

        'sensitive_fields' => [
            '*' => [
                'password',
                '*_token',
                'token',
                'secret',
                '*_secret',
            ],

            'App\Models\User' => [
                'drives_licence',
            ],
        ],

        /**
         * The date format to use for date fields
         */
        'date_format' => 'j F Y',

        /**
         * The date format to use for datetime fields
         */
        'date_time_format' => 'j F Y, H:i:s',
    ],
];
