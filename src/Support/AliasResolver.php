<?php

declare(strict_types=1);

namespace TTBooking\Formster\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Reflector;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use TTBooking\Formster\Attributes\Alias;
use UnitEnum;

class AliasResolver
{
    /**
     * @param  object|class-string  $objectOrClass
     */
    public static function resolveAlias(object|string $objectOrClass, ?string $type = null): string
    {
        return Reflector::getClassAttribute($objectOrClass, Alias::class)->alias
            ?? static::guessAlias($objectOrClass, $type);
    }

    /**
     * @param  object|class-string  $objectOrClass
     */
    protected static function guessAlias(object|string $objectOrClass, ?string $type = null): string
    {
        $type ??= match (true) {
            is_subclass_of($objectOrClass, Model::class) => 'Model',
            is_subclass_of($objectOrClass, UnitEnum::class) => 'Enum',
            default => throw new InvalidArgumentException('Class type cannot be determined.'),
        };

        return (string) str(is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass)->whenStartsWith(
            $namespace = app()->getNamespace().Str::plural($type).'\\',
            static fn (Stringable $class) => $class->after($namespace)->replace('\\', '')->snake(),
            static fn (Stringable $class) => $class->replace('\\', '')->snake(),
        );
    }
}
