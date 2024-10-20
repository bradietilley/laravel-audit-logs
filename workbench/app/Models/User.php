<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLogs\Concerns\HasAuditLog;
use BradieTilley\AuditLogs\Contracts\WithAuditLog;
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
