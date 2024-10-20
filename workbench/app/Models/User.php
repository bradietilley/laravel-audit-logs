<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLog\Concerns\HasAuditLog;
use BradieTilley\AuditLog\Contracts\WithAuditLog;
use Illuminate\Foundation\Auth\User as AuthUser;

class User extends AuthUser implements WithAuditLog
{
    use HasAuditLog;

    public $table = 'users';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
