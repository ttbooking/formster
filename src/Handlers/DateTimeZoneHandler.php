<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use DateTimeZone;
use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;

class DateTimeZoneHandler implements PropertyHandler
{
    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return $property->type->contains(DateTimeZone::class);
    }

    public function component(): string
    {
        return 'formster::form.timezone';
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = new DateTimeZone((string) $request->string($this->property->variableName));
    }

    public function validate(Request $request): bool
    {
        return true;
    }
}
