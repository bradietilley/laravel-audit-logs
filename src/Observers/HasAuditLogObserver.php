<?php

namespace BradieTilley\AuditLog\Observers;

use BradieTilley\AuditLog\Contracts\WithAuditLog;
use Illuminate\Database\Eloquent\Model;

class HasAuditLogObserver
{
    public function created(Model&WithAuditLog $model): void
    {
        $model->getAuditLogger()->run('created');
    }

    public function updated(Model&WithAuditLog $model): void
    {
        $model->getAuditLogger()->run('updated');
    }

    public function saved(Model&WithAuditLog $model): void
    {
        $model->getAuditLogger()->run('saved');
    }

    public function deleted(Model&WithAuditLog $model): void
    {
        $model->getAuditLogger()->run('deleted');
    }

    public function trashed(Model&WithAuditLog $model): void
    {
        $model->getAuditLogger()->run('trashed');
    }

    public function forceDeleted(Model&WithAuditLog $model): void
    {
        $model->getAuditLogger()->run('forceDeleted');
    }

    public function restored(Model&WithAuditLog $model): void
    {
        $model->getAuditLogger()->run('restored');
    }
}
