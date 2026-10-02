<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

use function TTBooking\Formster\Support\prop_param;

class IntegerHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    public function __construct(protected FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect([
            'int', 'integer', 'positive-int', 'negative-int', 'non-positive-int', 'non-negative-int',
        ])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.number';
    }

    public function validationRules(): string|array
    {
        [$min, $max] = $this->getBounds();

        $minRule = isset($min) ? "|min:$min" : '';
        $maxRule = isset($max) ? "|max:$max" : '';

        return $this->property->mergeValidationRules('required|integer'.$minRule.$maxRule);
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->integer($this->property->variableName);
    }

    /**
     * @return array{int|null, int|null}
     */
    public function getBounds(): array
    {
        /** @var array{int|null, int|null} $bounds */
        $bounds = [
            prop_param($this->property, 0, 'min'),
            prop_param($this->property, 1, 'max'),
        ];

        /** @var array{int|null, int|null} */
        return match ($this->namedType()->name) {
            'positive-int' => static::mergeBounds([1, null], $bounds),
            'negative-int' => static::mergeBounds([null, -1], $bounds),
            'non-positive-int' => static::mergeBounds([null, 0], $bounds),
            'non-negative-int' => static::mergeBounds([0, null], $bounds),
            default => $bounds,
        };
    }

    /**
     * @param  array{int|null, int|null}  $bounds1
     * @param  array{int|null, int|null}  $bounds2
     * @return array{int|null, int|null}
     */
    protected static function mergeBounds(array $bounds1, array $bounds2): array
    {
        return [
            ($bounds1[0] ?? PHP_INT_MIN) < ($bounds2[0] ?? PHP_INT_MIN) ? $bounds2[0] : $bounds1[0],
            ($bounds1[1] ?? PHP_INT_MAX) > ($bounds2[1] ?? PHP_INT_MAX) ? $bounds2[1] : $bounds1[1],
        ];
    }
}
