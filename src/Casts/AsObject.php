<?php

declare(strict_types=1);

namespace TTBooking\Formster\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use TTBooking\Formster\Contracts\Mappable;

/**
 * @template T of object
 *
 * @implements CastsAttributes<T, T>
 */
class AsObject implements CastsAttributes
{
    /**
     * @param  class-string<T>  $class
     * @param  'json'|'array'  $format
     */
    public function __construct(protected string $class, protected string $format = 'json') {}

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>|null  $value
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            /** @var array<string, mixed> $value */
            $value = Json::decode($value);
        }

        return is_subclass_of($this->class, Mappable::class)
            ? $this->class::fromArray($value)
            : new $this->class(...$value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, array<mixed>>|string|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array|string|null
    {
        if (is_null($value)) {
            return null;
        }

        $data = Arr::from($value);

        /** @var array<string, array<mixed>>|string */
        return match ($this->format) {
            'json' => Json::encode($data),
            'array' => [$key => $data],
            default => throw new InvalidArgumentException('Invalid format value.'),
        };
    }
}
