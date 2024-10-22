<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLogs\Concerns\HasAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use Illuminate\Foundation\Auth\User as AuthUser;

class User extends AuthUser implements WithAuditLogs
{
    use HasAuditLogs;

    public $table = 'users';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
