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
            $aura->summary !== '' ? $aura->summary : $this->summary,
            $aura->description !== '' ? $aura->description : $this->description,
            $this->properties->merge($aura->properties)->values(),
        );
    }
}
