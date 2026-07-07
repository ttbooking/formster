<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class FloatHandler implements PropertyHandler
{
    public function __construct(public FinalAuraProperty $property) {}

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
        return $this->property->mergeValidationRules('required|numeric');
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->float($this->property->variableName);
    }
}
