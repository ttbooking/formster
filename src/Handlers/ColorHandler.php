<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Types\Color;

class ColorHandler implements PropertyHandler
{
    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return $property->type->contains(Color::class);
    }

    public function component(): string
    {
        return 'formster::form.color';
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = new Color((string) $request->string($this->property->variableName));
    }

    public function validate(Request $request): bool
    {
        return true;
    }
}
