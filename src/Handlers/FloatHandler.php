<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

use function TTBooking\Formster\Support\prop_param;

class FloatHandler implements PropertyHandler
{
    public function __construct(protected FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect(['float', 'double', 'real'])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.decimal';
    }

    public function validationRules(): string|array
    {
        /** @var float|int|null $min */
        $min = prop_param($this->property, 0, 'min');

        /** @var float|int|null $max */
        $max = prop_param($this->property, 1, 'max');

        $minRule = isset($min) ? "|min:$min" : '';
        $maxRule = isset($max) ? "|max:$max" : '';

        return $this->property->mergeValidationRules('required|numeric'.$minRule.$maxRule);
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->float($this->property->variableName);
    }
}
