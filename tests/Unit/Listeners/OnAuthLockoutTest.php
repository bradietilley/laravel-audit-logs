<?php

use BradieTilley\AuditLogs\Listeners\OnAuthLockout;
use BradieTilley\AuditLogs\Models\AuditLog;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;

test('logs are written for Illuminate\Auth\Events\Lockout', function () {
    $event = new Lockout(request());

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
        'action' => OnAuthLockout::ACTION,
        'data' => [
            //
        ],
    ]);
});
