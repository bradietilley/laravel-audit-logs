<?php

namespace BradieTilley\AuditLogs;

use Illuminate\Support\Str;

class AuditLogUtil
{
    /**
     * @var array<class-string, string>
     */
    protected static array $names = [];

    /**
     * @param array<class-string, string> $names
     */
    public static function usingNames(array $names): void
    {
        static::$names = [
            ...static::$names,
            ...$names,
        ];
    }

    /**
     * @return array<class-string, string>
     */
    public static function getNames(): array
    {
        return static::$names;
    }

    /**
     * @param class-string $model
     */
    public static function getName(string $model): string
    {
        return static::$names[$model] ??= Str::of($model)->afterLast('\\')->headline();
    }
}
