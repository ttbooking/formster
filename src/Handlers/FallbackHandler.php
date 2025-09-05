<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;

class FallbackHandler implements PropertyHandler
{
    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return true;
    }

    public function component(): string
    {
        return 'formster::form.disclaimer';
    }

    public function handle(object $object, Request $request): void
    {
        @trigger_error("Property type {$this->property->type} unsupported.");
    }

    public function validate(Request $request): bool
    {
        return true;
    }
}
