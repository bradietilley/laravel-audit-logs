<?php

namespace BradieTilley\AuditLogs\Observers;

use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use Illuminate\Database\Eloquent\Model;

class HasAuditLogsObserver
{
    public function created(Model&WithAuditLogs $model): void
    {
        $model->getAuditLogger()->run('created');
    }

    public function updated(Model&WithAuditLogs $model): void
    {
        $model->getAuditLogger()->run('updated');
    }

    public function saved(Model&WithAuditLogs $model): void
    {
        $model->getAuditLogger()->run('saved');
    }

    public function deleted(Model&WithAuditLogs $model): void
    {
        $model->getAuditLogger()->run('deleted');
    }

    public function trashed(Model&WithAuditLogs $model): void
    {
        $model->getAuditLogger()->run('trashed');
    }

    public function forceDeleted(Model&WithAuditLogs $model): void
    {
        $model->getAuditLogger()->run('forceDeleted');
    }

    public function restored(Model&WithAuditLogs $model): void
    {
        $model->getAuditLogger()->run('restored');
    }
}
