<?php

declare(strict_types=1);

namespace TTBooking\Formster\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use TTBooking\Formster\Contracts\Mappable;

/**
 * @template TKey of array-key
 * @template TValue of object
 *
 * @implements CastsAttributes<Collection<TKey, TValue>, iterable<TKey, TValue>>
 */
class AsObjectCollection implements CastsAttributes
{
    /**
     * @param  class-string<TValue>  $class
     * @param  'json'|'array'  $format
     */
    public function __construct(protected string $class, protected string $format = 'json') {}

    /**
     * Cast the given value.
     *
     * @param  iterable<TKey, array<string, mixed>>|string|null  $value
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Collection
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            /** @var array<TKey, array<string, mixed>> $value */
            $value = Json::decode($value);
        }

        /** @var Collection<TKey, TValue> */
        return collect($value)->map(
            is_subclass_of($this->class, Mappable::class)
                ? $this->class::fromArray(...)
                : fn (array $item) => new $this->class(...$item)
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, array<TKey, array<mixed>>>|string|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array|string|null
    {
        if (is_null($value)) {
            return null;
        }

        $collection = collect($value)->map(Arr::from(...));

        /** @var array<string, array<TKey, array<mixed>>>|string */
        return match ($this->format) {
            'json' => Json::encode($collection->jsonSerialize()),
            'array' => [$key => $collection->all()],
            default => throw new InvalidArgumentException('Invalid format value.'),
        };
    }
}
