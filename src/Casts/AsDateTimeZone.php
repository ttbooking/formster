<?php

declare(strict_types=1);

namespace TTBooking\Formster\Casts;

use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use TTBooking\Formster\Types\DateTimeZone;
use TypeError;

/**
 * @implements CastsAttributes<DateTimeZone, \DateTimeZone>
 */
class AsDateTimeZone implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws Exception
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?DateTimeZone
    {
        if (is_null($value)) {
            return null;
        }

        if (! is_string($value)) {
            throw new RuntimeException('Timezone must be a string.');
        }

        return new DateTimeZone($value);
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
        if (! $value instanceof \DateTimeZone) {
            throw new TypeError(sprintf(
                'Cannot assign %s to property %s::$%s of type %s',
                get_debug_type($value), get_class($model), $key, \DateTimeZone::class
            ));
        }

        return $value->getName();
    }
}
