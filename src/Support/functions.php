<?php

declare(strict_types=1);

namespace TTBooking\Formster\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use ReflectionEnumUnitCase;
use TTBooking\Formster\Entities\AuraProperty;
use UnitEnum;

/**
 * @param  object|class-string  $objectOrClass
 * @param  null|string|Closure(): string  $fallback
 */
function prop_desc(object|string $objectOrClass, string $property, null|string|Closure $fallback = null): string
{
    $translator = app('translator');
    $type = is_subclass_of($objectOrClass, Model::class) ? 'model' : 'object';
    $alias = AliasResolver::resolveAlias($objectOrClass);
    $appKey = sprintf('formster.%s.%s.%s', $type, $alias, Str::snake($property));
    $pkgKey = sprintf('formster::%s.%s.%s', $type, $alias, Str::snake($property));
    $fallback ??= static fn () => Str::headline($property);

    /** @var string */
    return $translator->has($appKey) ? $translator->get($appKey) : (
        $translator->has($pkgKey) ? $translator->get($pkgKey) : value($fallback)
    );
}

/**
 * @param  null|string|Closure(): string  $fallback
 */
function enum_desc(UnitEnum $case, null|string|Closure $fallback = null): string
{
    $translator = app('translator');
    $alias = AliasResolver::resolveAlias($case, 'Enum');
    $appKey = sprintf('formster.enum.%s.%s', $alias, Str::snake($case->name));
    $pkgKey = sprintf('formster::enum.%s.%s', $alias, Str::snake($case->name));

    $fallback ??= static function () use ($case) {
        $refCase = new ReflectionEnumUnitCase($case, $case->name);
        $docComment = $refCase->getDocComment();

        return $docComment ? trim($docComment, "/* \n\r\t\v\0") : Str::headline($case->name);
    };

    /** @var string */
    return $translator->has($appKey) ? $translator->get($appKey) : (
        $translator->has($pkgKey) ? $translator->get($pkgKey) : value($fallback)
    );
}

function prop_val(AuraProperty $property, ?object $object = null): mixed
{
    return isset($object) ? $object->{$property->variableName} : $property->defaultValue;
}

function number_format(int|float $number, ?int $precision = null): string
{
    if (extension_loaded('intl') && false !== $result = Number::format($number, $precision)) {
        return $result;
    }

    return \number_format($number, $precision ?? detect_precision($number));
}

function detect_precision(int|float $number): int
{
    preg_match('/.*\.(.*)/', (string) $number, $digits);

    return isset($digits[1]) ? strlen($digits[1]) : 0;
}
