<?php

use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('requests are logged', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.org',
        'password' => '',
    ]);

    $user = User::create([
        'name' => 'User',
        'email' => 'user@example.org',
        'password' => '',
    ]);

    $this->actingAs($admin);
    AuditLog::query()->delete();
    $logs = collect();

    Event::listen(fn (MessageLogged $event) => $logs[] = [
        'message' => $event->message,
        'context' => $event->context,
    ]);
    $this->get('/request-logging-test/'.$user->id);

    expect(AuditLog::count())->toBe(1);
    $auditLog = AuditLog::first();

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs->first()?->only([
        'model_type',
        'model_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'Done something',
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);

    expect($admin->actionLogs()->count())->toBe(1);
    expect($admin->actionLogs->first()->only([
        'model_type',
        'model_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'Done something',
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);

    expect($logs->all())->toBe([
        [
            'message' => 'Done something',
            'context' => [
                'log' => [
                    'id' => $auditLog->id,
                    'ulid' => $auditLog->ulid,
                ],
                'request' => [
                    'ip' => '127.0.0.1',
                    'route' => 'request-logging-test-route',
                    'path' => 'http://localhost',
                    'middleware' => [
                        'web',
                    ],
                    'user_agent' => 'Symfony',
                ],
                'user' => [
                    'id' => $admin->id,
                    'email' => $admin->email,
                    'name' => $admin->name,
                ],
                'data' => [],
            ],
        ],
    ]);
});
