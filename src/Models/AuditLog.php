<?php

namespace BradieTilley\AuditLogs\Models;

use BradieTilley\AuditLogs\Casts\IpAddressCast;
use BradieTilley\AuditLogs\Observers\AuditLogObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;

/**
 * @property-read int $id
 * @property-read string $ulid
 *
 * @property-read string $model_type
 * @property-read string|int $model_id
 * @property-read string|int|null $user_id
 * @property-read string $action
 * @property-read ?string $ip
 * @property-read array $data
 *
 * @property-read Model $model
 * @property-read ?User $user
 */
#[ObservedBy(AuditLogObserver::class)]
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public const TYPE_AUDIT = 'audit';
    public const TYPE_ACTIVITY = 'activity';

    protected $guarded = [
        'id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ip' => IpAddressCast::class,
            'data' => 'array',
        ];
    }

    /**
     * Scope audit logs by the given IP
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeIp(Builder $query, string $ip): Builder
    {
        if (empty($ip)) {
            return $query;
        }

        $binary = inet_pton($ip);

        if ($binary === false) {
            // Return no results for invalid IP addresses
            return $query->whereRaw('1 = 0'); /** @phpstan-ignore-line */
        }

        return $query->where('ip', $binary);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }
}
