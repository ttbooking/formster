<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class IntegerHandler implements PropertyHandler
{
    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect(['int', 'integer'])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.number';
    }

    public function validationRules(): string|array
    {
        return $this->property->mergeValidationRules('required|integer');
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->integer($this->property->variableName);
    }
}
