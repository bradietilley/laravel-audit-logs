<?php

use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;

test('generic activity logger will record updates', function () {
    $user = User::withoutEvents(function () {
        return User::create([
            'name' => 'John Doe',
            'email' => 'dojathej@example.org',
            'password' => Hash::make('b8b8e49c4t3gr4'),
        ]);
    });

    expect($user->auditLogs()->count())->toBe(0);

    $user->update([
        'name' => 'Foo Bar',
    ]);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs->first()->only([
        'model_type',
        'model_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'Name set to `Foo Bar`',
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});

test('generic activity logger will record updates but will not include certain field values', function () {
    $user = User::withoutEvents(function () {
        return User::create([
            'name' => 'John Doe',
            'email' => 'dojathej@example.org',
            'password' => Hash::make('b8b8e49c4t3gr4'),
        ]);
    });

    expect($user->auditLogs()->count())->toBe(0);

    $user->update([
        'password' => Hash::make('new password'),
    ]);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs->first()->only([
        'model_type',
        'model_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'Password updated',
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});

test('generic activity logger will record updates but will not include long strings', function () {
    $user = User::withoutEvents(function () {
        return User::create([
            'name' => 'John Doe',
            'email' => 'dojathej@example.org',
            'password' => Hash::make('b8b8e49c4t3gr4'),
        ]);
    });

    expect($user->auditLogs()->count())->toBe(0);

    $user->update([
        'name' => str_repeat('a', 255),
    ]);

    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs->first()->only([
        'model_type',
        'model_id',
        'action',
        'ip',
        'type',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'Name updated',
        'ip' => '127.0.0.1',
        'type' => 'activity',
    ]);
});

test('generic activity logger will not record superfluous fields', function () {
    $user = User::withoutEvents(function () {
        return User::create([
            'name' => 'John Doe',
            'email' => 'dojathej@example.org',
            'password' => Hash::make('b8b8e49c4t3gr4'),
        ]);
    });

    expect($user->auditLogs()->count())->toBe(0);

    $user->update([
        'created_at' => now()->subDay(),
    ]);

    expect($user->auditLogs()->count())->toBe(0);
});
