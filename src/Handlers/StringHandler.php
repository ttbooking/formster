<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class StringHandler implements PropertyHandler
{
    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect(['string', 'non-empty-string', 'class-string'])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.text';
    }

    public function validationRules(): string|array
    {
        return $this->property->mergeValidationRules('required|string');
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = (string) $request->string($this->property->variableName);
    }
}
