<?php

declare(strict_types=1);

namespace TTBooking\Formster\Types;

use Illuminate\Contracts\Database\Eloquent\Castable;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;
use TTBooking\Formster\Casts\AsColor;
use TTBooking\Formster\Contracts\Comparable;

class Color implements Castable, Comparable, JsonSerializable, Stringable
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^#[a-zA-Z0-9]{6}$/', $value)) {
            throw new InvalidArgumentException(
                'Color should be represented in a 6-digit hexadecimal format prefixed with the hash sign.'
            );
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function sameAs(mixed $that): bool
    {
        return $that instanceof $this && $that->value === $this->value;
    }

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     * @return class-string<AsColor>
     */
    public static function castUsing(array $arguments): string
    {
        return AsColor::class;
    }
}
