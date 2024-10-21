<?php

namespace BradieTilley\AuditLogs;

use BradieTilley\AuditLogs\Listeners\OnAuthAttempting;
use BradieTilley\AuditLogs\Listeners\OnAuthAuthenticated;
use BradieTilley\AuditLogs\Listeners\OnAuthCurrentDeviceLogout;
use BradieTilley\AuditLogs\Listeners\OnAuthFailed;
use BradieTilley\AuditLogs\Listeners\OnAuthLockout;
use BradieTilley\AuditLogs\Listeners\OnAuthLogin;
use BradieTilley\AuditLogs\Listeners\OnAuthOtherDeviceLogout;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordReset;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordResetLinkSent;
use BradieTilley\AuditLogs\Listeners\OnAuthRegistered;
use BradieTilley\AuditLogs\Listeners\OnAuthValidated;
use BradieTilley\AuditLogs\Listeners\OnAuthVerified;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Validated;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AuditLogServiceProvider extends PackageServiceProvider
{
    public const EVENTS = [
        Attempting::class => OnAuthAttempting::class,
        Authenticated::class => OnAuthAuthenticated::class,
        CurrentDeviceLogout::class => OnAuthCurrentDeviceLogout::class,
        Failed::class => OnAuthFailed::class,
        Lockout::class => OnAuthLockout::class,
        Login::class => OnAuthLogin::class,
        OtherDeviceLogout::class => OnAuthOtherDeviceLogout::class,
        PasswordReset::class => OnAuthPasswordReset::class,
        PasswordResetLinkSent::class => OnAuthPasswordResetLinkSent::class,
        Registered::class => OnAuthRegistered::class,
        Validated::class => OnAuthValidated::class,
        Verified::class => OnAuthVerified::class,
    ];

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

        foreach (static::EVENTS as $event => $listener) {
            Event::listen($event, $listener);
        }
    }
}
