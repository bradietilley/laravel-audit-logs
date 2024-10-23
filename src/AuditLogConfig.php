<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Models\AuditLog;
use BradieTilley\AuditLogs\Observers\HasAuditLogsObserver;

class AuditLogConfig
{
    /** @var array<string, mixed> */
    protected static array $cache = [];

    protected static function get(string $key, mixed $default = null): mixed
    {
        return static::$cache[$key] ??= config("audit-logs.{$key}", $default);
    }

    public static function clearCache(): void
    {
        static::$cache = [];
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
     * Get the audit log observer class to use
     *
     * @return class-string<HasAuditLogsObserver>
     */
    public static function getObserverClass(): string
    {
        /** @phpstan-ignore-next-line */
        return static::get('classes.observer', HasAuditLogsObserver::class);
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

    /**
     * The date format to use for a date field in a changelog
     */
    public static function getDateFormat(): string
    {
        /** @phpstan-ignore-next-line */
        return static::get('changes.date_format', 'j F Y');
    }

    /**
     * The date format to use for a datetime field in a changelog
     */
    public static function getDateTimeFormat(): string
    {
        /** @phpstan-ignore-next-line */
        return static::get('changes.date_time_format', 'j F Y, H:i:s');
    }

    /**
     * Get the length to truncate string fields to.
     *
     * @return array<string, array<string, int>>
     */
    public static function getTruncateStringLengths(): array
    {
        /** @phpstan-ignore-next-line */
        return static::get('changes.truncate_string_lengths', []);
    }

    /**
     * Get the fields to ignore.
     *
     * @return array<string, array<string, string>>
     */
    public static function getIgnoredFields(): array
    {
        /** @phpstan-ignore-next-line */
        return static::get('changes.ignored_fields', []);
    }

    /**
     * Get the sensitive fields to redact.
     *
     * @return array<string,array<string, string>>
     */
    public static function getSensitiveFields(): array
    {
        /** @phpstan-ignore-next-line */
        return static::get('changes.sensitive_fields', []);
    }
}
