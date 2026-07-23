<?php

use BradieTilley\AuditLogs\Listeners\OnAuthAttempting;
use BradieTilley\AuditLogs\Listeners\OnAuthAuthenticated;
use BradieTilley\AuditLogs\Listeners\OnAuthCurrentDeviceLogout;
use BradieTilley\AuditLogs\Listeners\OnAuthFailed;
use BradieTilley\AuditLogs\Listeners\OnAuthLockout;
use BradieTilley\AuditLogs\Listeners\OnAuthLogin;
use BradieTilley\AuditLogs\Listeners\OnAuthOtherDeviceLogout;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordReset;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordResetLinkSent;
use BradieTilley\AuditLogs\Listeners\OnAuthRegistered;
use BradieTilley\AuditLogs\Listeners\OnAuthValidated;
use BradieTilley\AuditLogs\Listeners\OnAuthVerified;
use BradieTilley\AuditLogs\Loggers\ChangeLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use BradieTilley\AuditLogs\Observers\HasAuditLogsObserver;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Validated;
use Illuminate\Auth\Events\Verified;

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

        /**
         * The change logger class to use when formatting model updates
         *
         * @var class-string<\BradieTilley\AuditLogs\Loggers\ChangeLogger>
         */
        'change_logger' => ChangeLogger::class,
    ],

    /**
     * The log channel to write to. Set to null to persist only to the database
     * without writing to a log stream.
     *
     * @var ?string
     */
    'log_channel' => null,

    /**
     * The attribute to include in all default authentication logs such as login,
     * password reset, etc.
     *
     * @var string
     */
    'user_identifier' => 'email',

    /**
     * Authentication events to listen for. Map an event class to a listener class.
     * Set a listener to null or false to disable that event.
     *
     * @var array<class-string, class-string|false|null>
     */
    'auth_events' => [
        Attempting::class => OnAuthAttempting::class,
        Authenticated::class => OnAuthAuthenticated::class,
        CurrentDeviceLogout::class => OnAuthCurrentDeviceLogout::class,
        Failed::class => OnAuthFailed::class,
        Lockout::class => OnAuthLockout::class,
        Login::class => OnAuthLogin::class,
        OtherDeviceLogout::class => OnAuthOtherDeviceLogout::class,
        PasswordReset::class => OnAuthPasswordReset::class,
        PasswordResetLinkSent::class => OnAuthPasswordResetLinkSent::class,
        Registered::class => OnAuthRegistered::class,
        Validated::class => OnAuthValidated::class,
        Verified::class => OnAuthVerified::class,
    ],

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
