<?php

use BradieTilley\AuditLogs\Listeners\OnAuthLogin;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('logs are written for Illuminate\Auth\Events\Login', function () {
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
