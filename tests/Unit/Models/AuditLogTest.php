<?php

use BradieTilley\AuditLogs\AuditLogger;
use BradieTilley\AuditLogs\Models\AuditLog;
use Workbench\App\Models\User;

test('an audit log morphs to a resource and an actioner', function () {
    $admin = User::withoutEvents(fn () => User::create([
        'name' => 'Test',
        'email' => 'test@example.org',
        'password' => '',
    ]));

    $user = User::withoutEvents(fn () => User::create([
        'name' => 'Test',
        'email' => 'test2@example.org',
        'password' => '',
    ]));

    $this->actingAs($admin);
    AuditLogger::write('Test', $user);

    $log = AuditLog::firstOrFail();

    expect($log->user->is($admin))->toBeTrue();
    expect($log->model->is($user))->toBeTrue();
});

test('audit logs can be scoped by ip address', function () {
    $a1 = AuditLogger::write('1');
    $a2 = AuditLogger::write('2');
    $a3 = AuditLogger::write('3');
    $a4 = AuditLogger::write('4');

    $a1->update([
        'ip' => '127.0.0.234',
    ]);
    $a2->update([
        'ip' => '127.0.0.231',
    ]);
    $a3->update([
        'ip' => '127.0.0.231',
    ]);
    $a4->update([
        'ip' => '127.0.0.234',
    ]);

    expect(AuditLog::query()->ip('127.0.0.234')->pluck('action')->all())->toBe([
        '1',
        '4',
    ]);

    expect(AuditLog::query()->ip('127.0.0.231')->pluck('action')->all())->toBe([
        '2',
        '3',
    ]);
});
