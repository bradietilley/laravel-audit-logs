<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Foundation\Auth\User;

class AuditLogConfig
{
    protected static function get(string $key, mixed $default = null): mixed
    {
        return config("audit-logs.{$key}", $default);
    }

    /**
     * @return class-string<User>
     */
    public static function getUserModel(): string
    {
        return static::get('models.user', User::class);
    }

    /**
     * @return class-string<AuditLog>
     */
    public static function getAuditLogModel(): string
    {
        return static::get('models.audit_log', AuditLog::class);
    }

    /**
     * Get the log channel to write to (if specified)
     */
    public static function getLogChannel(): ?string
    {
        return static::get('log_channel');
    }

    /**
     * The attribute to include in all default authentication logs such as login,
     * password reset, etc.
     */
    public static function getUserIdentifier(): string
    {
        return static::get('user_identifier');
    }
}
