<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Psr\Log\LoggerInterface;

class AuditLogger
{
    public LoggerInterface $logger;

    /** @var array<string, mixed> */
    protected array $cache = [];

    public function __construct(
        public readonly Request $request,
        LogManager $log,
    ) {
        $this->logger = $log->channel(AuditLogConfig::getLogChannel());
    }

    public static function make(): AuditLogger
    {
        /** @var AuditLogger $instance */
        $instance = app(AuditLogger::class);

        return $instance;
    }

    /**
     * @param array<mixed> $data
     */
    public function record(?Model $model, string $action, string $type = AuditLog::TYPE_ACTIVITY, array $data = []): AuditLog
    {
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

        $this->logger->info($log->action, $data);
    }

    protected function runningInConsole(): bool
    {
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= App::runningInConsole();
    }

    public function user(): ?User
    {
        /** @phpstan-ignore-next-line */
        return $this->cache[__FUNCTION__] ??= Auth::user();
    }

    public function setUser(?User $user): static
    {
        $this->cache['user'] = $user;

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

    protected function route(): RoutingRoute
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
