<?php

use BradieTilley\AuditLogs\AuditLogUtil;
use Workbench\App\Models\AdministrativeUser;
use Workbench\App\Models\Staff;
use Workbench\App\Models\User;

beforeEach(function () {
    AuditLogUtil::clear();
});

test('model names are guessed', function () {
    expect(AuditLogUtil::getModelName(User::class))->toBe('User');
    expect(AuditLogUtil::getModelName(Staff::class))->toBe('Staff');
    expect(AuditLogUtil::getModelName(AdministrativeUser::class))->toBe('Administrative User');
});

test('model names are overrideable', function () {
    expect(AuditLogUtil::getModelNames())->toBe([]);
    expect(AuditLogUtil::getModelName(User::class))->toBe('User');

    AuditLogUtil::usingModelNames([
        User::class => 'Person',
    ]);

    expect(AuditLogUtil::getModelNames())->toBe([
        User::class => 'Person',
    ]);
    expect(AuditLogUtil::getModelName(User::class))->toBe('Person');

    AuditLogUtil::clear();

    expect(AuditLogUtil::getModelNames())->toBe([]);
    expect(AuditLogUtil::getModelName(User::class))->toBe('User');
});

test('field names are guessed', function (string $input, string $expect) {
    $actual = AuditLogUtil::getFieldName($input);

    expect($actual)->toBe($expect);
})->with([
    [
        'input' => 'avatar_id',
        'expect' => 'Avatar ID',
    ],
    [
        'input' => 'content',
        'expect' => 'Content',
    ],
    [
        'input' => 'is_something_enabled',
        'expect' => 'Is Something Enabled',
    ],
    [
        'input' => 'id',
        'expect' => 'ID',
    ],
    [
        'input' => 'uuid',
        'expect' => 'UUID',
    ],
    [
        'input' => 'ulid',
        'expect' => 'ULID',
    ],
]);

test('field names are overrideable', function () {
    expect(AuditLogUtil::getFieldNames())->toBe(AuditLogUtil::DEFAULT_FIELD_NAMES);
    expect(AuditLogUtil::getFieldName('bio'))->toBe('Bio');

    AuditLogUtil::usingFieldNames([
        'bio' => 'Biography',
    ]);

    expect(AuditLogUtil::getFieldNames())->toBe([
        ...AuditLogUtil::DEFAULT_FIELD_NAMES,
        'bio' => 'Biography',
    ]);
    expect(AuditLogUtil::getFieldName('bio'))->toBe('Biography');

    AuditLogUtil::clear();

    expect(AuditLogUtil::getFieldNames())->toBe(AuditLogUtil::DEFAULT_FIELD_NAMES);
    expect(AuditLogUtil::getFieldName('bio'))->toBe('Bio');
});
