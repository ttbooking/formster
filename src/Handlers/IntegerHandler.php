<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Concerns\MergesValidationRules;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;

class IntegerHandler implements PropertyHandler
{
    use MergesValidationRules;

    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return collect(['int', 'integer'])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.number';
    }

    public function validationRules(): string|array
    {
        return $this->mergeValidationRules('required|integer:strict');
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->integer($this->property->variableName);
    }
}
