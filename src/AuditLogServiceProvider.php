<?php

namespace BradieTilley\AuditLogs;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AuditLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('audit-logs')
            ->hasConfigFile()
            ->hasMigration('create_audit_logs_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AuditLogConfig::class, AuditLogConfig::class);
        $this->app->singleton(AuditLogRecorder::class, AuditLogRecorder::class);
    }
}
