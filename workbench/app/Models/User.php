<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLogs\Concerns\HasAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use Illuminate\Foundation\Auth\User as AuthUser;
use Workbench\App\Enums\UserStatusTestEnum;

class User extends AuthUser implements WithAuditLogs
{
    use HasAuditLogs;

    public $table = 'users';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'integer_field' => 'integer',
            'decimal_field' => 'decimal:2',
            'string_field' => 'string',
            'date_field' => 'date',
            'datetime_field' => 'datetime',
            'enum_field' => UserStatusTestEnum::class,
            'array_field' => 'array',
        ];
    }
}
