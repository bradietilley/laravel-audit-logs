<?php

namespace BradieTilley\AuditLogs\Contracts;

use BradieTilley\AuditLogs\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

interface AuditLogger
{
    /**
     * @param array<mixed> $data
     */
    public function record(string $action, ?Model $model = null, string $type = AuditLog::TYPE_ACTIVITY, array $data = []): ?AuditLog;

    /**
     * @param array<mixed>|(Closure(): array<mixed>) $data
     */
    public function recordOnce(string $action, ?Model $model = null, string $type = AuditLog::TYPE_ACTIVITY, array|Closure $data = []): ?AuditLog;

    public function setUser(?User $user): static;

    public function user(): ?User;

    public function setRunningInConsole(bool $runningInConsole = true): void;
}
