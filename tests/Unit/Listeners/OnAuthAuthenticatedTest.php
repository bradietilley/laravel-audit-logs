<?php

use BradieTilley\AuditLogs\AuditLogRecorder;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Event;
use Workbench\App\Models\User;

test('user is preloaded during Illuminate\Auth\Events\Authenticated', function () {
    $user = User::withoutEvents(
        fn () => User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => '',
        ]),
    );

    $recorder = AuditLogRecorder::make();

    $cache = (new ReflectionProperty($recorder, 'cache'))->getValue($recorder);
    expect($cache)->not->toHaveKey('user');

    $event = new Authenticated('web', $user);
    Event::dispatch($event);

    $cache = (new ReflectionProperty($recorder, 'cache'))->getValue($recorder);
    expect($cache)->toHaveKey('user');
    expect($user->is($cache['user']))->toBeTrue();
});
