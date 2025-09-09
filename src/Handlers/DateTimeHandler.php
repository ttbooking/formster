<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use DateTimeInterface;
use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;

class DateTimeHandler implements PropertyHandler
{
    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return $property->type->contains(DateTimeInterface::class);
    }

    public function component(): string
    {
        return 'formster::form.datetime';
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->date($this->property->variableName, 'Y-m-d\TH:i');
    }

    public function validate(Request $request): bool
    {
        return true;
    }
}
