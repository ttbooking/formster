<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class BooleanHandler implements PropertyHandler
{
    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect(['bool', 'boolean'])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.checkbox';
    }

    public function validationRules(): string|array
    {
        return $this->property->mergeValidationRules('sometimes|in:on');
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->has($this->property->variableName);
    }
}
