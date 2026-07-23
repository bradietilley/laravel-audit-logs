<?php

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\AuditLogger;
use BradieTilley\AuditLogs\Events\AuditLogRecorded;
use BradieTilley\AuditLogs\Loggers\ChangeLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Psr\Log\NullLogger;
use Workbench\App\Models\User;

test('AuditLogRecorded is dispatched when a log is recorded', function () {
    Event::fake([AuditLogRecorded::class]);

    $log = AuditLogger::write('Something happened');

    Event::assertDispatched(AuditLogRecorded::class, function (AuditLogRecorded $event) use ($log) {
        return $event->auditLog->is($log);
    });
});

test('stream logging is skipped when log_channel is null', function () {
    AuditLogConfig::clearCache();
    config()->set('audit-logs.log_channel', null);

    $this->app->forgetInstance(AuditLogger::class);

    $logger = AuditLogger::make();
    expect($logger->logger)->toBeInstanceOf(NullLogger::class);

    $logs = collect();
    Event::listen(fn (MessageLogged $event) => $logs[] = $event->message);

    AuditLogger::write('DB only');

    expect(AuditLog::count())->toBe(1);
    expect($logs)->toBeEmpty();
});

test('auth events can be disabled via config', function () {
    AuditLogConfig::clearCache();
    config()->set('audit-logs.auth_events.'.Login::class, false);

    expect(AuditLogConfig::getAuthEvents()[Login::class])->toBeFalse();
});

test('change logger class is resolved from config', function () {
    AuditLogConfig::clearCache();

    expect(AuditLogConfig::getChangeLoggerClass())->toBe(ChangeLogger::class);

    $user = User::withoutEvents(fn () => User::create([
        'name' => 'Test',
        'email' => 'change-logger@example.org',
        'password' => '',
    ]));

    expect(ChangeLogger::make($user))->toBeInstanceOf(ChangeLogger::class);
});
