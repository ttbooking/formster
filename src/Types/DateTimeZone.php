<?php

declare(strict_types=1);

namespace TTBooking\Formster\Types;

use Illuminate\Contracts\Database\Eloquent\Castable;
use JsonSerializable;
use Stringable;
use TTBooking\Formster\Casts\AsDateTimeZone;
use TTBooking\Formster\Contracts\Comparable;

/**
 * @template TTimezoneGroup of int|string = \DateTimeZone::ALL
 * @template TGroupByRegion of bool = (TTimezoneGroup is \DateTimeZone::ALL ? true : false)
 */
class DateTimeZone extends \DateTimeZone implements Castable, Comparable, JsonSerializable, Stringable
{
    public function __toString(): string
    {
        return $this->getName();
    }

    public function jsonSerialize(): string
    {
        return $this->getName();
    }

    public function sameAs(mixed $that): bool
    {
        return $that instanceof $this && $that->getName() === $this->getName();
    }

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     * @return class-string<AsDateTimeZone>
     */
    public static function castUsing(array $arguments): string
    {
        return AsDateTimeZone::class;
    }
}
