<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use DateTimeZone;
use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class DateTimeZoneHandler implements PropertyHandler
{
    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return $property->type->contains(DateTimeZone::class);
    }

    public function component(): string
    {
        return 'formster::form.timezone';
    }

    public function validationRules(): string|array
    {
        return $this->property->mergeValidationRules('required|timezone');
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = new DateTimeZone((string) $request->string($this->property->variableName));
    }
}
