<?php

use BradieTilley\AuditLogs\Listeners\OnAuthVerified;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('logs are written for Illuminate\Auth\Events\Verified', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $event = new Verified($user);

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
        'action' => OnAuthVerified::ACTION,
        'data' => [
            'event' => [
                'email' => 'john.doe@example.org',
            ],
        ],
    ]);
});
