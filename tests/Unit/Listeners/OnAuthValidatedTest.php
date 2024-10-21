<?php

use BradieTilley\AuditLogs\Listeners\OnAuthValidated;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('logs are written for Illuminate\Auth\Events\Validated', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new Validated('web', $user);

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
        'action' => OnAuthValidated::ACTION,
        'data' => [
            'event' => [
                'guard' => 'web',
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});
