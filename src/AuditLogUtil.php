<?php

namespace BradieTilley\AuditLogs;

use Illuminate\Support\Str;

class AuditLogUtil
{
    /**
     * @var array<class-string, string>
     */
    protected static array $modelNames = [];

    /**
     * @var array<string, string>
     */
    protected static array $fieldNames = [];

    /**
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
     * @return array<class-string, string>
     */
    public static function getModelNames(): array
    {
        return static::$modelNames;
    }

    /**
     * @return array<string, string>
     */
    public static function getFieldNames(): array
    {
        return static::$fieldNames;
    }

    /**
     * @param class-string $model
     */
    public static function getModelName(string $model): string
    {
        return static::$modelNames[$model] ??= Str::of($model)->afterLast('\\')->headline();
    }

    public static function getFieldName(string $field): string
    {
        return static::$fieldNames[$field] ??= Str::of($field)->headline();
    }
}
