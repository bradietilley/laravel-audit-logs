<?php

namespace BradieTilley\AuditLogs\Loggers;

use BackedEnum;
use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\AuditLogUtil;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Curate a list of changes made to a model during the `updated` event.
 */
class ChangeLogger
{
    protected const ENCODING = 'UTF-8';

    /** @var array<string, mixed> */
    public array $casts = [];

    public function __construct(public readonly Model $model)
    {
        $this->casts = $this->model->getCasts();
    }

    /**
     * Create a new instance of the compiler
     */
    public static function make(Model $model): self
    {
        /** @var static $instance */
        $instance = app(self::class, [
            'model' => $model,
        ]);

        return $instance;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        /** @phpstan-ignore-next-line */
        return Collection::make($this->getChanges())
            ->map(function (mixed $value, string $field) {
                $label = AuditLogUtil::getFieldName($field);

                if ($this->isIgnored($field)) {
                    return null;
                }

                if ($this->isSensitive($field)) {
                    return "{$label} updated";
                }

                if (is_string($value) || is_int($value)) {
                    if ($this->isDateField($field) || $this->isDateTimeField($field)) {
                        $value = CarbonImmutable::parse((string) $value);
                    } elseif ($this->isEnumField($field)) {
                        /** @var class-string<BackedEnum> $enumClass */
                        $enumClass = $this->casts[$field];

                        $value = $enumClass::tryFrom($value) ?? $value;
                    } elseif ($this->isIterableField($field)) {
                        return "{$label} updated";
                    }
                }

                if (is_string($value)) {
                    $value = json_encode($value) ?: '';
                    $value = mb_substr($value, 1, -1);
                    $length = mb_strlen($value, static::ENCODING);
                    $truncateLength = $this->getTruncateLength($field);

                    if ($length > $truncateLength) {
                        $value = Str::limit($value, $truncateLength, '...', true);

                        return "{$label} set to `{$value}` ({$length} characters)";
                    }

                    return "{$label} set to `{$value}`";
                }

                if (is_int($value) || is_float($value)) {
                    return "{$label} set to {$value}";
                }

                if (is_null($value)) {
                    return "{$label} removed";
                }

                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';

                    return "{$label} set to {$value}";
                }

                if ($value instanceof DateTimeInterface) {
                    $format = $this->isDateField($field) ? AuditLogConfig::getDateFormat() : AuditLogConfig::getDateTimeFormat();
                    $value = $value->format($format);

                    return "{$label} set to {$value}";
                }

                if ($value instanceof BackedEnum) {
                    $readable = $value->value;

                    if (method_exists($value, 'label')) {
                        $readable = call_user_func([$value, 'label']);
                    } elseif (method_exists($value, 'name')) {
                        $readable = call_user_func([$value, 'name']);
                    }

                    return "{$label} set to {$readable}";
                }

                return "{$label} updated";
            })
            ->filter()
            ->all();
    }

    /**
     * Determine if the given field should be ignored
     */
    protected function isIgnored(string $field): bool
    {
        $fields = AuditLogConfig::getIgnoredFields();
        $fields = [
            ...$fields['*'] ?? [],
            ...$fields[$this->model::class] ?? [],
        ];

        return in_array($field, $fields);
    }

    /**
     * Determine if the given field is sensitive and should be redacted
     */
    protected function isSensitive(string $field): bool
    {
        $fields = AuditLogConfig::getSensitiveFields();
        $fields = [
            ...$fields['*'] ?? [],
            ...$fields[$this->model::class] ?? [],
        ];

        return Str::is($fields, $field);
    }

    public function isDateField(string $field): bool
    {
        return in_array($this->casts[$field] ?? null, [
            'date',
            'immutable_date',
        ]);
    }

    public function isDateTimeField(string $field): bool
    {
        return in_array($this->casts[$field] ?? null, [
            'datetime',
            'immutable_datetime',
        ]);
    }

    /**
     * Get the length to truncate this field to.
     */
    public function getTruncateLength(string $field): int
    {
        $config = AuditLogConfig::getTruncateStringLengths();

        /**
         * At the most specific, you might have defined a specific model and a specific field
         */
        if (isset($config[$this->model::class][$field])) {
            return $config[$this->model::class][$field];
        }

        /**
         * Next most specific, you might have defined a specific model but *any* field
         */
        if (isset($config[$this->model::class]['*'])) {
            return $config[$this->model::class]['*'];
        }

        /**
         * Next most specific, you might have defined *any* model and a specific field
         */
        if (isset($config['*'][$field])) {
            return $config['*'][$field];
        }

        /**
         * Least specific, you might have defined *any* model and *any* field
         */
        if (isset($config['*']['*'])) {
            return $config['*']['*'];
        }

        /**
         * Misconfiguration defualt
         */
        return 100;
    }

    public function isEnumField(string $field): bool
    {
        $cast = $this->casts[$field] ?? null;

        if (! is_string($cast)) {
            return false;
        }

        if (! class_exists($cast)) {
            return false;
        }

        $class = new ReflectionClass($cast);

        return $class->isEnum();
    }

    public function isIterableField(string $field): bool
    {
        $cast = $this->casts[$field] ?? null;

        if (! is_string($cast)) {
            return false;
        }

        return Str::is([
            'array',
            'collection',
            'json',
        ], $cast);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getChanges(): array
    {
        return $this->model->getChanges();
    }
}
