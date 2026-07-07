<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Attribute;
use Illuminate\Support\Collection;
use LogicException;
use TypeError;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Aura
{
    /** @var Collection<string, AuraProperty> */
    public Collection $properties;

    /**
     * @param  iterable<array-key, AuraProperty>  $properties
     */
    final public function __construct(
        public string $summary = '',
        public string $description = '',
        iterable $properties = [],
        public string $viewPolicy = 'view',
        public string $updatePolicy = 'update',
    ) {
        $this->properties = collect($properties)->keyBy(static function (AuraProperty $property, int|string $key) {
            if (is_int($key)) {
                isset($property->variableName)
                    || throw new LogicException('Property name cannot be determined.');

                return $property->variableName;
            }

            ! isset($property->variableName)
                || $property->variableName === $key
                || throw new LogicException('Property name mismatch.');

            return $property->variableName = $key;
        });
    }

    /**
     * @throws TypeError
     */
    public function finalize(): FinalAura
    {
        return new FinalAura(
            summary: $this->summary,
            description: $this->description,
            properties: $this->properties->map->finalize(),
            viewPolicy: $this->viewPolicy,
            updatePolicy: $this->updatePolicy,
        );
    }

    public function merge(self $aura): static
    {
        return new static(
            summary: $aura->summary !== '' ? $aura->summary : $this->summary,
            description: $aura->description !== '' ? $aura->description : $this->description,
            properties: $this->properties
                ->map(static fn (AuraProperty $value, string $key) => isset($aura->properties[$key])
                    ? $value->merge($aura->properties[$key])
                    : $value
                )
                ->union($aura->properties),
            viewPolicy: $aura->viewPolicy !== 'view' ? $aura->viewPolicy : $this->viewPolicy,
            updatePolicy: $aura->updatePolicy !== 'update' ? $aura->updatePolicy : $this->updatePolicy,
        );
    }
}
