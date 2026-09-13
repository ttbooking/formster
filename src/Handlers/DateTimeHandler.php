<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;
use TTBooking\Formster\Enums\TemporalComponent;

use function TTBooking\Formster\Support\prop_param;

class DateTimeHandler implements PropertyHandler
{
    public function __construct(protected FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return $property->type->contains(DateTimeInterface::class);
    }

    public function component(): string
    {
        return match (prop_param($this->property, 0, 'variant') ?? TemporalComponent::Both) {
            TemporalComponent::Date => 'formster::form.date',
            TemporalComponent::Time => 'formster::form.time',
            default => 'formster::form.datetime',
        };
    }

    public function validationRules(): string|array
    {
        return $this->property->mergeValidationRules([
            ...($this->property->type->nullable ? ['present', 'nullable'] : ['required']),
            Rule::date()->format($this->dateFormat(false)),
        ]);
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->date($this->property->variableName, $this->dateFormat());
    }

    protected function dateFormat(bool $reset = true): string
    {
        return match (prop_param($this->property, 0, 'variant') ?? TemporalComponent::Both) {
            TemporalComponent::Date => $reset ? '!Y-m-d' : 'Y-m-d',
            TemporalComponent::Time => $reset ? '!H:i' : 'H:i',
            default => 'Y-m-d\TH:i',
        };
    }
}
