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
        'data',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'User updated',
        'ip' => '127.0.0.1',
        'type' => 'activity',
        'data' => [
            'changes' => [
                'name' => 'Name set to `Foo Bar`',
            ],
        ],
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
        'data',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'User updated',
        'ip' => '127.0.0.1',
        'type' => 'activity',
        'data' => [
            'changes' => [
                'password' => 'Password updated',
            ],
        ],
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

    $expect = str_repeat('a', 100);
    expect($user->auditLogs()->count())->toBe(1);
    expect($user->auditLogs->first()->only([
        'model_type',
        'model_id',
        'action',
        'ip',
        'type',
        'data',
    ]))->toBe([
        'model_type' => User::class,
        'model_id' => $user->id,
        'action' => 'User updated',
        'ip' => '127.0.0.1',
        'type' => 'activity',
        'data' => [
            'changes' => [
                'name' => "Name set to `{$expect}...` (255 characters)",
            ],
        ],
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
        'updated_at' => now()->subDay(),
    ]);

    expect($user->auditLogs()->count())->toBe(0);
});
