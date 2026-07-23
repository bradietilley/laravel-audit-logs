<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Contracts\AuditLogger as AuditLoggerContract;
use BradieTilley\AuditLogs\Events\AuditLogRecorded;
use BradieTilley\AuditLogs\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AuditLogger implements AuditLoggerContract
{
    public LoggerInterface $logger;

    /**
     * A cache of resolved data points that don't require resolving more than once per request lifecycle.
     *
     * @var array<string, mixed>
     */
    protected array $cache = [];

    /**
     * A cache of events that have already run that should only log once.
     *
     * @var array<string, AuditLog|null>
     */
    protected array $once = [];

    /**
     * Number of levels of nested `withoutLogging` events.
     *
     * Each invocation of `withoutLogging` will increase this number during
     * the callback and reduce the number afterwards.
     */
    protected static int $withoutLogging = 0;

    public function __construct()
    {
        $channel = AuditLogConfig::getLogChannel();

        $this->logger = filled($channel)
            ? Log::channel($channel)
            : new NullLogger;
    }

    protected function request(): Request
    {
        return app(Request::class);
    }

    /**
     * Static constructor
     */
    public static function make(): AuditLoggerContract
    {
        /** @var AuditLoggerContract $instance */
        $instance = app(AuditLoggerContract::class);

        return $instance;
    }

    /**
     * Run the callback without logging anything
     */
    public static function withoutLogging(Closure $callback): mixed
    {
        try {
            static::$withoutLogging++;

            return $callback();
        } finally {
            static::$withoutLogging--;
        }
    }

    /**
     * Check if there's a `withoutLogging` callback running currently
     */
    public static function isWithoutLogging(): bool
    {
        return static::$withoutLogging > 0;
    }

    /**
     * Record an audit log.
     *
     * Static shortcut to `record()`
     *
     * @param array<mixed> $data
     */
    public static function write(string $action, ?Model $model = null, string $type = AuditLog::TYPE_ACTIVITY, array $data = []): ?AuditLog
    {
        return static::make()->record($action, $model, $type, $data);
    }

    /**
     * Record an audit log once this request lifecycle, unique by model and action.
     *
     * Static shortcut to `recordOnce()`
     *
     * @param array<mixed> $data
     */
    public static function writeOnce(string $action, ?Model $model = null, string $type = AuditLog::TYPE_ACTIVITY, array $data = []): ?AuditLog
    {
        return static::make()->recordOnce($action, $model, $type, $data);
    }

    /**
     * Record an audit log
     *
     * @param array<mixed> $data
     */
    public function record(string $action, ?Model $model = null, string $type = AuditLog::TYPE_ACTIVITY, array $data = []): ?AuditLog
    {
        if (static::isWithoutLogging()) {
            return null;
        }

        $class = AuditLogConfig::getAuditLogModel();

        $log = new $class();
        $log->fill([
            'model_type' => $model?->getMorphClass(),
            'model_id' => $model?->getKey(),
            'user_type' => $this->getUserMorphClass(),
            'user_id' => $this->getUserId(),
            'ip' => $this->getRequestIp(),
            'action' => $action,
            'type' => $type,
            'data' => $data,
        ]);

        $log->save();

        $this->writeLog($log, $data);

        Event::dispatch(new AuditLogRecorded($log));

        return $log;
    }

    /**
     * Record an audit log once this request lifecycle, unique by model and action.
     *
     * @param array<mixed>|(Closure(): array<mixed>) $data
     */
    public function recordOnce(string $action, ?Model $model = null, string $type = AuditLog::TYPE_ACTIVITY, array|Closure $data = []): ?AuditLog
    {
        $key = $model?->getMorphClass().':'.$model?->getKey().':'.$action;

        return $this->once[$key] ??= $this->record($action, $model, $type, value($data));
    }

    /**
     * Write a verbose log to the log channel/stream
     *
     * @param array<mixed> $data
     */
    protected function writeLog(AuditLog $log, array $data): void
    {
        if (AuditLogConfig::getLogChannel() === null) {
            return;
        }

        $data = [
            'log' => [
                'id' => $log->id,
                'ulid' => $log->ulid,
            ],
            'request' => [
                'ip' => $this->getRequestIp(),
                'route' => $this->getRequestRoute(),
                'path' => $this->getRequestPath(),
                'middleware' => $this->getRequestMiddleware(),
                'user_agent' => $this->getRequestUserAgent(),
            ],
            'user' => [
                'id' => $this->getUserId(),
                'email' => $this->getUserEmail(),
                'name' => $this->getUserName(),
            ],
            'data' => $data,
        ];

        if ($this->runningInConsole()) {
            unset($data['request']);
        }

        $this->logger->info($log->action, $data);
    }

    public function setRunningInConsole(bool $runningInConsole = true): void
    {
        $this->cache['runningInConsole'] = $runningInConsole;
    }

    protected function runningInConsole(): bool
    {
        /** @phpstan-ignore-next-line */
        return $this->cache['runningInConsole'] ??= App::runningInConsole() && ! App::runningUnitTests();
    }

    public function user(): ?User
    {
        /** @phpstan-ignore-next-line */
        return $this->cache['user'] ??= Auth::user();
    }

    public function setUser(?User $user): static
    {
        $this->cache['user'] = $user;

        unset($this->cache['getUserMorphClass']);
        unset($this->cache['getUserId']);
        unset($this->cache['getUserEmail']);
        unset($this->cache['getUserName']);

        return $this;
    }

    protected function getUserMorphClass(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->user()?->getMorphClass();
    }

    protected function getUserId(): int|string|null
    {
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->user()?->getKey();
    }

    protected function getUserEmail(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->user()?->getAttribute('email');
    }

    protected function getUserName(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= rescue(
            fn () => $this->user()?->name, /** @phpstan-ignore-line */
            report: false,
        );
    }

    protected function route(): ?RoutingRoute
    {
        return Route::current();
    }

    protected function getRequestIp(): ?string
    {
        return $this->request()->ip();
    }

    protected function getRequestRoute(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->route()?->getName();
    }

    /**
     * @return array<int, string>|null
     */
    protected function getRequestMiddleware(): ?array
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->route()?->gatherMiddleware();
    }

    protected function getRequestPath(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->request()->fullUrl();
    }

    protected function getRequestUserAgent(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->request()->header('User-Agent');
    }
}
