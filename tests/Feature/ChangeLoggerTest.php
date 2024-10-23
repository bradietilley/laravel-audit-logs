<?php

use BradieTilley\AuditLogs\AuditLogConfig;
use BradieTilley\AuditLogs\Loggers\ChangeLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Enums\UserStatusTestEnum;
use Workbench\App\Models\Staff;
use Workbench\App\Models\User;

test('the change logger will record updated fields', function (array $updates, array $changes) {
    $user = User::withoutEvents(function () {
        return User::create([
            'name' => 'John Doe',
            'email' => 'dojathej@example.org',
            'password' => Hash::make('b8b8e49c4t3gr4'),
            'integer_field' => 0,
            'decimal_field' => 0,
            'string_field' => 'old',
            'date_field' => Carbon::now()->subDay(),
            'datetime_field' => Carbon::now()->subDay(),
            'enum_field' => UserStatusTestEnum::Pending,
            'array_field' => [],
            'foreign_key_id' => null,
        ]);
    });

    expect($user->auditLogs()->count())->toBe(0);

    $user->update($updates);

    if (empty($changes)) {
        expect($user->auditLogs()->count())->toBe(0);

        return;
    }

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
            'changes' => $changes,
        ],
    ]);
})->with([
    'will record string field changes' => [
        'updates' => [
            'name' => 'Foo Bar',
        ],
        'changes' => [
            'name' => 'Name set to `Foo Bar`',
        ],
    ],
    'will record integer field changes' => [
        'updates' => [
            'integer_field' => 1,
        ],
        'changes' => [
            'integer_field' => 'Integer Field set to 1',
        ],
    ],
    'will record null field changes' => [
        'updates' => [
            'string_field' => null,
        ],
        'changes' => [
            'string_field' => 'String Field removed',
        ],
    ],
    'will record boolean / true field changes' => [
        'updates' => [
            'boolean_field' => true,
        ],
        'changes' => [
            'boolean_field' => 'Boolean Field set to true',
        ],
    ],
    'will record boolean / false field changes' => [
        'updates' => [
            'boolean_field' => false,
        ],
        'changes' => [
            'boolean_field' => 'Boolean Field set to false',
        ],
    ],
    'will record enum field changes' => [
        'updates' => [
            'enum_field' => UserStatusTestEnum::Active,
        ],
        'changes' => [
            'enum_field' => 'Enum Field set to Active',
        ],
    ],
    'will record date field changes' => [
        'updates' => [
            'date_field' => Carbon::now(),
        ],
        'changes' => [
            'date_field' => 'Date Field set to '.Carbon::now()->format('j F Y'),
        ],
    ],
    'will record datetime field changes' => [
        'updates' => [
            'datetime_field' => $now = Carbon::now(),
        ],
        'changes' => [
            'datetime_field' => 'Datetime Field set to '.$now->format('j F Y, H:i:s'),
        ],
    ],
    'will record array field changes' => [
        'updates' => [
            'array_field' => [
                'some' => 'thing',
            ],
        ],
        'changes' => [
            'array_field' => 'Array Field updated',
        ],
    ],
    'will record foreign key field changes' => [
        'updates' => [
            'foreign_key_id' => 1,
        ],
        'changes' => [
            'foreign_key_id' => 'Foreign Key ID set to 1',
        ],
    ],
    'will redact sensitive field values' => [
        'updates' => [
            'password' => 'new password',
        ],
        'changes' => [
            'password' => 'Password updated',
        ],
    ],
    'will truncate long strings' => [
        'updates' => [
            'name' => str_repeat('a', 255),
        ],
        'changes' => [
            'name' => sprintf('Name set to `%s...` (255 characters)', str_repeat('a', 100)),
        ],
    ],
    'will not record superfluous fields' => [
        'updates' => [
            'updated_at' => Carbon::now()->subDay(),
        ],
        'changes' => [], // no audit log
    ],
]);

test('truncate length can be derived from configuration', function () {
    $config = [
        '*' => [
            '*' => 123,

            'name' => 124,
        ],

        Staff::class => [
            '*' => 125,

            'name' => 126,
        ],
    ];

    config([
        'audit-logs.changes.truncate_string_lengths' => $config,
    ]);
    AuditLogConfig::clearCache();

    $user = new User([
        'name' => '',
    ]);
    $userChangeLogger = new ChangeLogger($user);

    $staff = new Staff([
        'name' => '',
    ]);
    $staffChangeLogger = new ChangeLogger($staff);

    /** Catch-all model + catch-all field */
    expect($userChangeLogger->getTruncateLength('content'))->toBe(123);
    /** Catch-all model + specific field */
    expect($userChangeLogger->getTruncateLength('name'))->toBe(124);
    /** Specific model + catch-all field */
    expect($staffChangeLogger->getTruncateLength('content'))->toBe(125);
    /** Specific model + specific field */
    expect($staffChangeLogger->getTruncateLength('name'))->toBe(126);
});
