<?php

use BradieTilley\AuditLogs\Listeners\OnAuthRegistered;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('logs are written for Illuminate\Auth\Events\Registered', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new Registered($user);

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
        'action' => OnAuthRegistered::ACTION,
        'data' => [
            'event' => [
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});
