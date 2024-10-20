<?php

use BradieTilley\AuditLog\Casts\IpAddressCast;
use Workbench\App\Models\User;

test('ip addresses can be cast to binary', function (string $ip) {
    $user = new User();
    $expect = inet_pton($ip);

    $castable = new IpAddressCast();
    $actual = $castable->set($user, 'ip', $ip, []);

    expect($actual)->toBe($expect);
})->with([
    '127.0.0.1',
    '123.45.67.89',
]);
