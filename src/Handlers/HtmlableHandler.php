<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class HtmlableHandler implements PropertyHandler
{
    public function __construct(protected FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return $property->type->contains(Htmlable::class);
    }

    public function component(): string
    {
        return 'formster::form.html';
    }

    public function validationRules(): array
    {
        return [];
    }

    public function handle(object $object, Request $request): void
    {
        //
    }
}
