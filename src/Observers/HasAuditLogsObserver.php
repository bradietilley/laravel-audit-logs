<?php

namespace BradieTilley\AuditLogs\Observers;

use BradieTilley\AuditLogs\AuditLogger;
use BradieTilley\AuditLogs\AuditLogUtil;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use BradieTilley\AuditLogs\Loggers\ModelChangeCompiler;
use Illuminate\Database\Eloquent\Model;

class HasAuditLogsObserver
{
    public function __construct(public readonly AuditLogger $logger)
    {
    }

    public function created(Model&WithAuditLogs $model): void
    {
        $this->logger->record($model, action: "{$this->name($model)} created");
    }

    public function updated(Model&WithAuditLogs $model): void
    {
        $changes = ModelChangeCompiler::make($model)->toArray();

        if (empty($changes)) {
            return;
        }

        $this->logger->record($model, action: "{$this->name($model)} updated", data: [
            'changes' => $changes,
        ]);
    }

    public function deleted(Model&WithAuditLogs $model): void
    {
        $this->logger->record($model, action: "{$this->name($model)} deleted");
    }

    public function forceDeleted(Model&WithAuditLogs $model): void
    {
        $this->logger->record($model, action: "{$this->name($model)} force deleted");
    }

    public function restored(Model&WithAuditLogs $model): void
    {
        $this->logger->record($model, action: "{$this->name($model)} restored");
    }

    protected function name(Model $model): string
    {
        return AuditLogUtil::getName($model::class);
    }
}
