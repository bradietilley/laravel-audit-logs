<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Listeners\OnAuthAttempting;
use BradieTilley\AuditLogs\Listeners\OnAuthFailed;
use BradieTilley\AuditLogs\Listeners\OnAuthLogin;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordReset;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordResetLinkSent;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\Facades\Event;
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
        $this->app->singleton(AuditLogRecorder::class, AuditLogRecorder::class);

        Event::listen(Attempting::class, OnAuthAttempting::class);
        Event::listen(Login::class, OnAuthLogin::class);
        Event::listen(Failed::class, OnAuthFailed::class);
        Event::listen(PasswordReset::class, OnAuthPasswordReset::class);
        Event::listen(PasswordResetLinkSent::class, OnAuthPasswordResetLinkSent::class);
    }
}
