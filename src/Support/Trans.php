<?php

declare(strict_types=1);

namespace TTBooking\Formster\Support;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
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
final readonly class Trans implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Stringable
{
    /** @var array<string, string> */
    private array $messages;

    /**
     * @param  array<string, (Closure(string): string)|scalar|null>  $replace
     */
    public function __construct(string $key, array $replace = [], ?bool $prefix = null)
    {
        /** @var array<string, string> $messages */
        $messages = is_string($trans = trans($key, $replace))
            ? [$prefix !== false ? $key : Str::afterLast($key, '.') => $trans]
            : Arr::dot($trans, $prefix ? $key.'.' : '');

        $this->messages = $messages;
    }

    public function __toString(): string
    {
        return implode("\n", $this->messages);
    }

    public function toArray(): array
    {
        return $this->messages;
    }

    public function getIterator(): Generator
    {
        yield from $this->messages;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->messages[$offset]);
    }

    public function offsetGet(mixed $offset): string
    {
        return $this->messages[$offset];
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
