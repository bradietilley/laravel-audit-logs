<?php

namespace BradieTilley\AuditLog;

use BradieTilley\AuditLog\Contracts\WithAuditLog;
use BradieTilley\AuditLog\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Psr\Log\LoggerInterface;

class AuditLogRecorder
{
    public LoggerInterface $logger;

    protected array $cache = [];

    public function __construct(
        public readonly Request $request,
        LogManager $log,
    ) {
        $this->logger = $log->channel(AuditLogConfig::getLogChannel());
    }

    public static function make(): AuditLogRecorder
    {
        return app(AuditLogRecorder::class);
    }

    public function record(Model&WithAuditLog $model, string $action, string $type, array $attributes = []): AuditLog
    {
        $class = AuditLogConfig::getAuditLogModel();

        $auditLogModel = $class::create([
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'user_id' => $this->getUserId(),
            'ip' => $this->getRequestIp(),
            'action' => $action,
            'type' => $type,
            'data' => [],
            ...$attributes,
        ]);

        $this->writeLog($auditLogModel, $attributes);

        return $auditLogModel;
    }

    protected function writeLog(AuditLog $log, array $attributes): void
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
            'attributes' => $attributes,
        ];

        $this->logger->info($log->action, $data);
    }

    protected function runningInConsole(): bool
    {
        return $this->cache[__METHOD__] ??= App::runningInConsole();
    }

    protected function user(): ?User
    {
        return $this->cache[__METHOD__] ??= Auth::user();
    }

    protected function getUserId(): ?int
    {
        return $this->cache[__METHOD__] ??= $this->user()?->getKey();
    }

    protected function getUserEmail(): ?string
    {
        return $this->cache[__METHOD__] ??= $this->user()?->getAttribute('email');
    }

    protected function getUserName(): ?string
    {
        return $this->cache[__METHOD__] ??= rescue(
            fn () => $this->user()?->name,
            report: false,
        );
    }

    protected function route(): RoutingRoute
    {
        return $this->cache[__METHOD__] ??= Route::current();
    }

    protected function getRequestIp(): string
    {
        return $this->cache[__METHOD__] ??= $this->request->ip();
    }

    protected function getRequestRoute(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->cache[__METHOD__] ??= $this->route()?->getName();
    }

    protected function getRequestMiddleware(): ?array
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->cache[__METHOD__] ??= $this->route()?->gatherMiddleware();
    }

    protected function getRequestPath(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->cache[__METHOD__] ??= $this->request->fullUrl();
    }

    protected function getRequestUserAgent(): ?string
    {
        if ($this->runningInConsole()) {
            return null;
        }

        return $this->cache[__METHOD__] ??= $this->request->header('User-Agent');
    }
}
