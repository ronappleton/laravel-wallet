<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IdColumnCast implements CastsAttributes
{
    /**
     * @param  int|string  $value
     */
    public function get(Model $model, string $key, $value, array $attributes): string|int
    {
        return Str::isUuid($value) ? (string) $value : (int) $value;
    }

    /**
     * @param  int|string  $value
     */
    public function set(Model $model, string $key, $value, array $attributes): string|int
    {
        return Str::isUuid($value) ? (string) $value : (int) $value;
    }
}
