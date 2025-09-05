<?php

declare(strict_types=1);

namespace TTBooking\Formster\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use TTBooking\Formster\Types\Color;
use TypeError;

/**
 * @implements CastsAttributes<Color, Color>
 */
class AsColor implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Color
    {
        if (is_null($value)) {
            return null;
        }

        if (! is_string($value)) {
            throw new RuntimeException('Color must be a string.');
        }

        return new Color($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        /** @phpstan-ignore instanceof.alwaysTrue */
        if (! $value instanceof Color) {
            throw new TypeError(sprintf(
                'Cannot assign %s to property %s::$%s of type %s',
                get_debug_type($value), get_class($model), $key, Color::class
            ));
        }

        return (string) $value;
    }
}
