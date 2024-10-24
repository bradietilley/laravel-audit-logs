<?php

use BradieTilley\AuditLogs\AuditLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use Workbench\App\Models\User;

test('audit logger can record logs once if specified to not log twice', function () {
    $logger = AuditLogger::make();
    expect(AuditLog::count())->toBe(0);

    $logger->recordOnce(null, 'test');
    expect(AuditLog::count())->toBe(1);

    $logger->recordOnce(null, 'test2');
    expect(AuditLog::count())->toBe(2);

    $logger->recordOnce(null, 'test2');
    expect(AuditLog::count())->toBe(2); // already logged

    $logger->recordOnce(null, 'test3');
    expect(AuditLog::count())->toBe(3);

    /**
     * Same again, but checking against a model
     */
    $user1 = User::withoutEvents(fn () => User::create([
        'name' => 'Test',
        'email' => 'test@example.org',
        'password' => '',
    ]));
    $user2 = User::withoutEvents(fn () => User::create([
        'name' => 'Test',
        'email' => 'test2@example.org',
        'password' => '',
    ]));
    expect(AuditLog::count())->toBe(3);

    $logger->recordOnce(null, 'test');
    expect(AuditLog::count())->toBe(3); // already logged

    $logger->recordOnce($user1, 'test');
    expect(AuditLog::count())->toBe(4);

    $logger->recordOnce($user1, 'test');
    expect(AuditLog::count())->toBe(4); // already logged

    $logger->recordOnce($user2, 'test');
    expect(AuditLog::count())->toBe(5);
});

test('audit logger can skip logging', function () {
    $logger = AuditLogger::make();
    expect(AuditLogger::isWithoutLogging())->toBe(false);
    expect(AuditLog::count())->toBe(0);

    AuditLogger::withoutLogging(function () {
        expect(AuditLogger::isWithoutLogging())->toBe(true);
        AuditLogger::write(null, 'Test');
        expect(AuditLogger::isWithoutLogging())->toBe(true);

        AuditLogger::withoutLogging(function () {
            expect(AuditLogger::isWithoutLogging())->toBe(true);
            AuditLogger::write(null, 'Test');
            expect(AuditLogger::isWithoutLogging())->toBe(true);
        });

        expect(AuditLogger::isWithoutLogging())->toBe(true);
        AuditLogger::write(null, 'Test');
        expect(AuditLogger::isWithoutLogging())->toBe(true);
    });

    expect(AuditLog::count())->toBe(0);
    expect(AuditLogger::isWithoutLogging())->toBe(false);

    AuditLogger::write(null, 'Test');
    expect(AuditLog::count())->toBe(1);
});
