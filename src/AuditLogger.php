<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Psr\Log\LoggerInterface;

class AuditLogger
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
     * @var array<string, mixed>
     */
    protected array $once = [];

    /**
     * Number of levels of nested `withoutLogging` events.
     *
     * Each invocation of `withoutLogging` will increase this number during
     * the callback and reduce the number afterwards.
     */
    protected static int $withoutLogging = 0;

    public function __construct(public readonly Request $request)
    {
        $this->logger = Log::channel(AuditLogConfig::getLogChannel());
    }

    /**
     * Static constructor
     */
    public static function make(): AuditLogger
    {
        /** @var AuditLogger $instance */
        $instance = app(AuditLogger::class);

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
     */
    public static function write(?Model $model, string $action, string $type = AuditLog::TYPE_ACTIVITY, array $data = []): ?AuditLog
    {
        return static::make()->record($model, $action, $type, $data);
    }

    /**
     * Record an audit log
     *
     * @param array<mixed> $data
     */
    public function record(?Model $model, string $action, string $type = AuditLog::TYPE_ACTIVITY, array $data = []): ?AuditLog
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

        return $log;
    }

    /**
     * Record an audit log once this request lifecycle, unique by model and action.
     *
     * @param array<mixed>|(Closure(): array<mixed>) $data
     */
    public function recordOnce(?Model $model, string $action, string $type = AuditLog::TYPE_ACTIVITY, array|Closure $data = []): mixed
    {
        $key = $model?->getMorphClass().':'.$model?->getKey().':'.$action;

        return $this->once[$key] ??= $this->record($model, $action, $type, value($data));
    }

    /**
     * Write a verbose log to the log channel/stream
     *
     * @param array<mixed> $data
     */
    protected function writeLog(AuditLog $log, array $data): void
    {
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

    protected function getUserId(): ?int
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
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= Route::current();
    }

    protected function getRequestIp(): string
    {
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->request->ip();
    }

    protected function getRequestRoute(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->route()?->getName();
    }

    /**
     * @return array<int, string>
     */
    protected function getRequestMiddleware(): ?array
    {
        if ($this->runningInConsole()) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->route()?->gatherMiddleware();
    }

    protected function getRequestPath(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->request->fullUrl();
    }

    protected function getRequestUserAgent(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= $this->request->header('User-Agent');
    }
}
