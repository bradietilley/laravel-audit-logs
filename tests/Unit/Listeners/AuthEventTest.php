<?php

use BradieTilley\AuditLogs\Listeners\OnAuthAttempting;
use BradieTilley\AuditLogs\Listeners\OnAuthFailed;
use BradieTilley\AuditLogs\Listeners\OnAuthLogin;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordReset;
use BradieTilley\AuditLogs\Listeners\OnAuthPasswordResetLinkSent;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('auth attempt event - user exists', function () {
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
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
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

test('auth attempt event - user does not exist', function () {
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
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
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

test('auth failed event - user exists', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new Failed('web', $user, [
        'email' => 'john.doe@example.org',
        'password' => '123456789',
    ]);

    Event::dispatch($event);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
        'user_id' => null,
        'model_type' => User::class,
        'model_id' => $user->id,
        'type' => AuditLog::TYPE_AUDIT,
        'action' => OnAuthFailed::ACTION,
        'data' => [
            'event' => [
                'guard' => 'web',
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});

test('auth failed event - user does not exist', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe2@example.org',
            'password' => '',
        ]),
    );

    $event = new Failed('web', null, [
        'email' => 'john.doe@example.org',
        'password' => '123456789',
    ]);

    AuditLog::query()->delete();
    Event::dispatch($event);

    expect(AuditLog::count())->toBe(1);
    expect(AuditLog::first()->only([
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
        'user_id' => null,
        'model_type' => null,
        'model_id' => null,
        'type' => AuditLog::TYPE_AUDIT,
        'action' => OnAuthFailed::ACTION,
        'data' => [
            'event' => [
                'guard' => 'web',
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});

test('auth successful event - user exists', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new Login('web', $user, $remember = (bool) mt_rand(0, 1));

    Event::dispatch($event);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
        'user_id' => null,
        'model_type' => User::class,
        'model_id' => $user->id,
        'type' => AuditLog::TYPE_AUDIT,
        'action' => OnAuthLogin::ACTION,
        'data' => [
            'event' => [
                'guard' => 'web',
                'remember' => $remember,
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});

test('auth password reset successful event - user exists', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new PasswordReset($user);

    Event::dispatch($event);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
        'user_id' => null,
        'model_type' => User::class,
        'model_id' => $user->id,
        'type' => AuditLog::TYPE_AUDIT,
        'action' => OnAuthPasswordReset::ACTION,
        'data' => [
            'event' => [
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});

test('auth password reset link sent event - user exists', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new PasswordResetLinkSent($user);

    Event::dispatch($event);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'user_id',
        'model_type',
        'model_id',
        'type',
        'action',
        'data',
    ]))->toBe([
        'user_id' => null,
        'model_type' => User::class,
        'model_id' => $user->id,
        'type' => AuditLog::TYPE_AUDIT,
        'action' => OnAuthPasswordResetLinkSent::ACTION,
        'data' => [
            'event' => [
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});
