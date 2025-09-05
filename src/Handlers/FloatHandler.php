<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;

class FloatHandler implements PropertyHandler
{
    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return collect(['float', 'double', 'real'])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.decimal';
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->float($this->property->variableName);
    }

    public function validate(Request $request): bool
    {
        return true;
    }
}
