<?php

namespace BradieTilley\AuditLogs\Observers;

use BradieTilley\AuditLogs\AuditLogUtil;
use BradieTilley\AuditLogs\Contracts\AuditLogger;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use BradieTilley\AuditLogs\Loggers\ChangeLogger;
use Illuminate\Database\Eloquent\Model;

class HasAuditLogsObserver
{
    public function __construct(public readonly AuditLogger $logger)
    {
    }

    public function created(Model&WithAuditLogs $model): void
    {
        $this->logger->record("{$this->name($model)} created", $model);
    }

    public function updated(Model&WithAuditLogs $model): void
    {
        $changes = ChangeLogger::make($model)->toArray();

        if (empty($changes)) {
            return;
        }

        $this->logger->record("{$this->name($model)} updated", $model, data: [
            'changes' => $changes,
        ]);
    }

    public function deleted(Model&WithAuditLogs $model): void
    {
        // SoftDeletes fires both `deleted` and `forceDeleted` on force delete —
        // skip here so only `forceDeleted` records the log.
        if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
            return;
        }

        $this->logger->record("{$this->name($model)} deleted", $model);
    }

    public function forceDeleted(Model&WithAuditLogs $model): void
    {
        $this->logger->record("{$this->name($model)} force deleted", $model);
    }

    public function restored(Model&WithAuditLogs $model): void
    {
        $this->logger->record("{$this->name($model)} restored", $model);
    }

    protected function name(Model $model): string
    {
        return AuditLogUtil::getModelName($model::class);
    }
}
