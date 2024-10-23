<?php

namespace BradieTilley\AuditLogs;

use Illuminate\Support\Str;

class AuditLogUtil
{
    public const DEFAULT_FIELD_NAMES = [
        'id' => 'ID',
        'ip' => 'IP',
        'uuid' => 'UUID',
        'ulid' => 'ULID',
    ];

    /**
     * @var array<class-string, string>
     */
    protected static array $modelNames = [];

    /**
     * @var array<string, string>
     */
    protected static array $fieldNames = self::DEFAULT_FIELD_NAMES;

    /**
     * Reset all maps
     */
    public static function clear(): void
    {
        static::$modelNames = [];
        static::$fieldNames = static::DEFAULT_FIELD_NAMES;
    }

    /**
     * Set the field name map
     *
     * @param array<class-string, string> $names
     */
    public static function usingModelNames(array $names): void
    {
        static::$modelNames = [
            ...static::$modelNames,
            ...$names,
        ];
    }

    /**
     * Get the model name map
     *
     * @return array<class-string, string>
     */
    public static function getModelNames(): array
    {
        return static::$modelNames;
    }

    /**
     * Get the human-readable name of the given PascalCase class
     *
     * @param class-string $model
     */
    public static function getModelName(string $model): string
    {
        return static::$modelNames[$model] ??= Str::of($model)->afterLast('\\')->headline()->toString();
    }

    /**
     * Set the field name map
     *
     * @param array<string, string> $names
     */
    public static function usingFieldNames(array $names): void
    {
        static::$fieldNames = [
            ...static::$fieldNames,
            ...$names,
        ];
    }

    /**
     * Get the field name map
     *
     * @return array<string, string>
     */
    public static function getFieldNames(): array
    {
        return static::$fieldNames;
    }

    /**
     * Get the human-readable name of the given snake_case field
     */
    public static function getFieldName(string $field): string
    {
        return static::$fieldNames[$field] ??= Str::of($field)->headline()->replaceEnd(' Id', ' ID')->toString();
    }
}
