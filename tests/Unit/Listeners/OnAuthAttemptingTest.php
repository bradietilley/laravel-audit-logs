<?php

use BradieTilley\AuditLogs\Listeners\OnAuthAttempting;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('logs are written for Illuminate\Auth\Events\Attempting - user exists', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new Attempting('web', [
        'email' => 'john.doe@example.org',
        'password' => '123456789',
    ], $remember = (bool) mt_rand(0, 1));

    Event::dispatch($event);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'user_type',
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
        'user_type' => null,
        'user_id' => null,
        'model_type' => User::class,
        'model_id' => $user->id,
        'type' => AuditLog::TYPE_AUDIT,
        'action' => OnAuthAttempting::ACTION,
        'data' => [
            'event' => [
                'guard' => 'web',
                'remember' => $remember,
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});

test('logs are written for Illuminate\Auth\Events\Attempting - user does not exist', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe2@example.org',
            'password' => '',
        ]),
    );

    $event = new Attempting('web', [
        'email' => 'john.doe@example.org',
        'password' => '123456789',
    ], $remember = (bool) mt_rand(0, 1));

    AuditLog::query()->delete();
    Event::dispatch($event);

    expect(AuditLog::count())->toBe(1);
    expect(AuditLog::first()->only([
        'user_type',
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
        'user_type' => null,
        'user_id' => null,
        'model_type' => null,
        'model_id' => null,
        'type' => AuditLog::TYPE_AUDIT,
        'action' => OnAuthAttempting::ACTION,
        'data' => [
            'event' => [
                'guard' => 'web',
                'remember' => $remember,
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});
