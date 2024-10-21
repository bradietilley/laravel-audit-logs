<?php

namespace BradieTilley\AuditLogs\Loggers;

use BackedEnum;
use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\AuditLogRecorder;
use BradieTilley\AuditLogs\Models\AuditLog;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Psr\Log\LoggerInterface;
use Throwable;

class ModelLogger
{
    use ForwardsCalls;

    protected LoggerInterface $logger;

    protected string $modelName;

    /** @var array<int, string> */
    protected array $logs = [];

    /** @var array<int, string> */
    protected array $changes = [];

    /**
     * Cache for models and their human readable names
     *
     * @var array<class-string, string>
     */
    protected static array $modelNames = [];

    final public function __construct(public readonly Model $model)
    {
        $this->logger = Log::channel(AuditLogConfig::getLogChannel());
        $this->modelName = $this->getDefaultModelName();
    }

    protected function getDefaultModelName(): string
    {
        return static::$modelNames[$this->model::class] ??= Str::of($this->model::class)->afterLast('\\')->headline();
    }

    /**
     * Static construct
     */
    public static function make(Model $model): static
    {
        return new static($model);
    }

    /**
     * Persist any queued logs upon destructing the activity logger
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * Run the event handler:
     *
     * - created
     * - updated
     * - deleted
     * - restored
     * - etc
     */
    public function run(string $event): void
    {
        try {
            $this->{$event}();

            $this->commit();
        } catch (Throwable $e) {
            $this->logger->error('Failed to log activity logs for model', [
                'event' => $event,
                'model_type' => $this->model::class,
                'model_id' => $this->model->getKey(),
                'error' => $e->getMessage(),
            ]);

            $this->logger->debug($e->getTraceAsString());
        }
    }

    /**
     * Record that the model was saved (created or updated)
     */
    protected function saved(): void
    {
    }

    /**
     * Record that the model was created
     */
    protected function created(): void
    {
        $this->recordSingleLog("{$this->modelName} created");
    }

    /**
     * Record that the model was updated
     */
    protected function updated(): void
    {
        foreach ($this->model->getChanges() as $field => $value) {
            $this->handleUpdatedField($field, $value);
        }
    }

    /**
     * Record that a field was updated
     */
    protected function handleUpdatedField(string $field, mixed $value): void
    {
        $label = ucwords(str_replace('_', ' ', $field));

        if ($this->isIrrelevant($field)) {
            return;
        }

        if ($this->isSensitive($field)) {
            $this->push("{$label} updated");

            return;
        }

        if (is_string($value)) {
            $value = strip_tags($value);
            $value = trim(preg_replace('/\s+/', ' ', $value) ?: '');

            if ($this->isTooLong($value)) {
                $this->push("{$label} updated");

                return;
            }

            $this->push(sprintf("{$label} set to `%s`", $value));

            return;
        }

        if (is_int($value) || is_float($value)) {
            $this->push(sprintf("{$label} set to %s", $value));

            return;
        }

        if (is_null($value)) {
            $this->push("{$label} removed");

            return;
        }

        if (is_bool($value)) {
            $this->push(sprintf("{$label} set to %s", $value ? 'true' : 'false'));

            return;
        }

        if ($value instanceof DateTimeInterface) {
            $this->push(sprintf("{$label} set to %s", $value->format('Y-m-d H:i:s')));

            return;
        }

        if ($value instanceof BackedEnum) {
            $readable = $value->value;

            if (method_exists($value, 'label')) {
                $readable = $value->label();
            } elseif (method_exists($value, 'name')) {
                $readable = $value->name();
            }

            $this->push(sprintf("{$label} set to %s", $readable));

            return;
        }

        $this->push("{$label} updated");
    }

    /**
     * Record that the model was deleted
     */
    protected function deleted(): void
    {
        $this->recordSingleLog("{$this->modelName} deleted");
    }

    /**
     * Record that the model was restored
     */
    protected function restored(): void
    {
        $this->recordSingleLog("{$this->modelName} restored");
    }

    /**
     * Record that the model was force deleted
     */
    protected function forceDeleted(): void
    {
        $this->recordSingleLog("{$this->modelName} force deleted");
    }

    /**
     * Record that the model was trashed
     */
    protected function trashed(): void
    {
        $this->recordSingleLog("{$this->modelName} trashed");
    }

    /**
     * Proxy methods calls to the model
     *
     * @param  string  $name
     * @param  array<mixed>  $arguments
     */
    public function __call($name, $arguments): mixed
    {
        return $this->forwardDecoratedCallTo($this->model, $name, $arguments);
    }

    /**
     * Proxy property getters to the model
     *
     * @param  string  $name
     */
    public function __get($name): mixed
    {
        return $this->model->{$name};
    }

    /**
     * Shortcut for wasChanged() on the model
     *
     * @param string|array<string, mixed>|null $attributes
     */
    public function wasChanged(string|array|null $attributes = null): bool
    {
        if (empty($this->changes)) {
            $this->changes = collect($this->model->getChanges())
                ->forget([
                    'id',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ])
                ->all();
        }

        foreach (Arr::wrap($attributes) as $attribute) {
            if (array_key_exists($attribute, $this->changes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record a single log
     */
    public function recordSingleLog(string $action): static
    {
        AuditLogRecorder::make()->record($this->model, $action, AuditLog::TYPE_ACTIVITY, []);

        return $this;
    }

    /**
     * Record a catch-all action event such as "Product updated" if
     * the model event didn't record any other logs
     */
    public function catchAllUpdated(string $action): static
    {
        if (! empty($this->logs)) {
            return $this;
        }

        if (empty($this->changes)) {
            return $this;
        }

        $this->recordSingleLog($action);

        return $this;
    }

    /**
     * Push a log to collate with other activity logs for the same model
     * to then record as a single log.
     */
    public function push(?string $action): static
    {
        if ($action === null) {
            return $this;
        }

        $this->logs[] = $action;

        return $this;
    }

    /**
     * Commit the current list of logs as a single log.
     */
    public function commit(): static
    {
        if (empty($this->logs)) {
            return $this;
        }

        $action = implode(', ', $this->logs);
        $this->logs = [];

        return $this->recordSingleLog($action);
    }

    /**
     * Format the given date as a string e.g. "9 January 2023" and optionally include
     * the time e.g. "9 January 2023, 20:10:05"
     */
    public function formatDate(string|DateTimeInterface|null $date, bool $time = false): string
    {
        if ($date === null) {
            return '--';
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        $format = 'j F Y';

        if ($time) {
            $format = "{$format}, H:i:s";
        }

        return $date->format($format);
    }

    protected function isIrrelevant(string $field): bool
    {
        return in_array($field, [
            'id',
            'ulid',
            'uuid',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);
    }

    protected function isSensitive(string $field): bool
    {
        return Str::is([
            'password',
            '*_token',
            'token',
            'secret',
            '*_secret',
        ], $field);
    }

    public function isTooLong(string $value): bool
    {
        return strlen($value) > 150;
    }
}
