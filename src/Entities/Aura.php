<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Illuminate\Support\Collection;

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
    ) {
        $this->properties = collect($properties)->keyBy('variableName');
    }

    public function merge(self $aura): static
    {
        return new static(
            $aura->summary !== '' ? $aura->summary : $this->summary,
            $aura->description !== '' ? $aura->description : $this->description,
            $this->properties->merge($aura->properties),
        );
    }
}
