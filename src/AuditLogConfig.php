<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Models\AuditLog;

class AuditLogConfig
{
    protected static function get(string $key, mixed $default = null): mixed
    {
        return config("audit-logs.{$key}", $default);
    }

    /**
     * Get the audit log model to use
     *
     * @return class-string<AuditLog>
     */
    public static function getAuditLogModel(): string
    {
        /** @phpstan-ignore-next-line */
        return static::get('models.audit_log', AuditLog::class);
    }

    /**
     * Get the log channel to write to (if specified)
     */
    public static function getLogChannel(): ?string
    {
        /** @phpstan-ignore-next-line */
        return static::get('log_channel');
    }

    /**
     * The attribute to include in all default authentication logs such as login,
     * password reset, etc.
     */
    public static function getUserIdentifier(): string
    {
        /** @phpstan-ignore-next-line */
        return static::get('user_identifier');
    }
}
