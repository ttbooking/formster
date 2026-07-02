<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Attribute;
use Illuminate\Support\Collection;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Aura
{
    /** @var Collection<string, AuraProperty> */
    public Collection $properties;

    /**
     * @param  iterable<int, AuraProperty>  $properties
     */
    final public function __construct(
        public string $summary = '',
        public string $description = '',
        iterable $properties = [],
        public string $viewPolicy = 'view',
        public string $updatePolicy = 'update',
    ) {
        $this->properties = collect($properties)->keyBy('variableName');
    }

    public function merge(self $aura): static
    {
        return new static(
            summary: $aura->summary !== '' ? $aura->summary : $this->summary,
            description: $aura->description !== '' ? $aura->description : $this->description,
            properties: $this->properties->merge($aura->properties)->values(),
            viewPolicy: $aura->viewPolicy !== 'view' ? $aura->viewPolicy : $this->viewPolicy,
            updatePolicy: $aura->updatePolicy !== 'update' ? $aura->updatePolicy : $this->updatePolicy,
        );
    }
}
