<?php

use BradieTilley\AuditLogs\Models\AuditLog;
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

    $this->get('/request-logging-test/'.$user->id);

    expect(AuditLog::count())->toBe(1);

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
});
