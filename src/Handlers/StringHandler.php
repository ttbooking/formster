<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use Illuminate\Support\Stringable;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class StringHandler implements PropertyHandler
{
    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect([
            'string', 'non-empty-string', 'class-string', Stringable::class,
        ])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.text';
    }

    public function validationRules(): string|array
    {
        return $this->property->mergeValidationRules('present|nullable|string');
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = (string) $request->string($this->property->variableName);
    }
}
