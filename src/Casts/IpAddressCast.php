<?php

namespace BradieTilley\AuditLogs\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class IpAddressCast implements CastsAttributes
{
    /**
     * Cast the given value to human-readable IP address.
     *
     * @param Model $model
     */
    public function get($model, string $key, $value, array $attributes): ?string
    {
        if (empty($value)) {
            return null;
        }

        return inet_ntop($value) ?: null;
    }

    /**
     * Prepare the given value for storage in binary format.
     *
     * @param Model $model
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if (empty($value)) {
            return null;
        }

        return inet_pton($value) ?: null;
    }
}
