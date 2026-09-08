<?php

declare(strict_types=1);

namespace TTBooking\Formster\Support;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Generator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use IteratorAggregate;
use Stringable;

/**
 * @implements Arrayable<string, string>
 * @implements ArrayAccess<string, string>
 * @implements IteratorAggregate<string, string>
 */
final readonly class Trans implements Arrayable, ArrayAccess, IteratorAggregate, Stringable
{
    /** @var array<string, string> */
    private array $trans;

    /**
     * @param  array<string, (Closure(string): string)|scalar|null>  $replace
     */
    public function __construct(string $key, array $replace = [], ?bool $prefix = null)
    {
        /** @var array<string, string> $trans */
        $trans = is_string($trans = trans($key, $replace))
            ? [$prefix !== false ? $key : Str::afterLast($key, '.') => $trans]
            : Arr::dot($trans, $prefix ? $key.'.' : '');

        $this->trans = $trans;
    }

    public function __toString(): string
    {
        return implode("\n", $this->trans);
    }

    public function toArray(): array
    {
        return $this->trans;
    }

    public function getIterator(): Generator
    {
        yield from $this->trans;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->trans[$offset]);
    }

    public function offsetGet(mixed $offset): string
    {
        return $this->trans[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Translated message is read-only.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Translated message is read-only.');
    }
}
