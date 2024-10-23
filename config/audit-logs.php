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
        'truncate_string_lengths' => [
            /** Any model */
            '*' => [
                /** Any string field */
                '*' => 100,

                //     'content' => 150, // Example for the 'content' field on any model
            ],

            // 'App\Models\User' => [
            //    '*' => 125, // Example for the User model but any field.
            //     'bio' => 150, // Example for the User model's `bio` field.
            // ],
        ],

        'ignored_fields' => [
            '*' => [
                'id',
                'updated_at',
                'deleted_at',
            ],

            // 'App\Models\User' => [ // Example for the User model's `remember_token` field.
            //     'remember_token',
            // ],
        ],

        'sensitive_fields' => [
            '*' => [
                'password',
                '*_token',
                'token',
                'secret',
                '*_secret',
            ],

            // 'App\Models\User' => [ // Example for the User model's `drives_licence_number` field.
            //     'drives_licence_number',
            // ],
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
