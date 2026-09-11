<?php

declare(strict_types=1);

namespace TTBooking\Formster\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use ReflectionEnumUnitCase;
use TTBooking\Formster\Entities\AuraNamedType;
use TTBooking\Formster\Entities\FinalAuraProperty;
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
    $fallback = $fallback ?: static fn () => str_starts_with($property, '_') ? '' : Str::headline($property);

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

function prop_val(FinalAuraProperty $property, ?object $object = null): mixed
{
    return isset($object) ? $object->{$property->variableName} : $property->defaultValue;
}

function prop_param(FinalAuraProperty $property, int $index, ?string $name = null): mixed
{
    $param = $property->type instanceof AuraNamedType
        ? $property->type->atomicParameters()->get($index)?->asConstExpr() : null;

    if (isset($param)) {
        return $param;
    }

    if (isset($property->meta['parameters']) && Arr::accessible($params = $property->meta['parameters'])) {
        /** @var array<mixed> $params */
        return isset($name)
            ? $params[$name] ?? $params[$index] ?? null
            : $params[$index] ?? null;
    }

    return null;
}

/**
 * @param  Model|string|array<mixed>|null  $default
 * @return string|array<mixed>|null
 */
function old(?string $key = null, Model|string|array|null $default = null): string|array|null
{
    if (isset($key)) {
        $key = strtr($key, ['[' => '.', ']' => '']);
    }

    return \old($key, $default);
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
