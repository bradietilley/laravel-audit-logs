<?php

use Workbench\App\Models\User;

test('logs are written when a model is created', function () {
    $user = User::create([
        'name' => 'test',
        'email' => 'test@example.org',
        'password' => '',
    ]);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'model_type',
        'model_id',
        'user_type',
        'user_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'user_type' => null,
        'user_id' => null,
        'action' => "User created",
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});

test('logs are written when a model is updated', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'test',
            'email' => 'test@example.org',
            'password' => '',
        ]),
    );

    expect($user->auditLogs()->count())->toBe(0);
    $user->update([
        'name' => 'New Name',
    ]);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'model_type',
        'model_id',
        'user_type',
        'user_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'user_type' => null,
        'user_id' => null,
        'action' => "User updated",
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});

test('logs are written when a model is deleted', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'test',
            'email' => 'test@example.org',
            'password' => '',
        ]),
    );

    expect($user->auditLogs()->count())->toBe(0);
    $user->delete();

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'model_type',
        'model_id',
        'user_type',
        'user_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'user_type' => null,
        'user_id' => null,
        'action' => "User deleted",
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});

test('logs are written when a model is force deleted', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'test',
            'email' => 'test@example.org',
            'password' => '',
        ]),
    );

    expect($user->auditLogs()->count())->toBe(0);
    $user->forceDelete();

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'model_type',
        'model_id',
        'user_type',
        'user_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'user_type' => null,
        'user_id' => null,
        'action' => "User force deleted",
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});

test('logs are written when a model is restored', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'test',
            'email' => 'test@example.org',
            'password' => '',
            'deleted_at' => now(),
        ]),
    );

    expect($user->auditLogs()->count())->toBe(0);
    $user->restore();

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs()->first()->only([
        'model_type',
        'model_id',
        'user_type',
        'user_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'user_type' => null,
        'user_id' => null,
        'action' => "User restored",
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});
