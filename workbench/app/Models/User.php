<?php

namespace Workbench\App\Models;

use BradieTilley\AuditLogs\Concerns\HasAuditLog;
use BradieTilley\AuditLogs\Concerns\HasPerformedAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLog;
use BradieTilley\AuditLogs\Contracts\WithPerformedAuditLogs;
use Illuminate\Foundation\Auth\User as AuthUser;

class User extends AuthUser implements WithAuditLog, WithPerformedAuditLogs
{
    use HasAuditLog;
    use HasPerformedAuditLogs;

    public $table = 'users';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
