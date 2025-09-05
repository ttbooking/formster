<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Illuminate\Support\Collection;

readonly class Aura
{
    /** @var Collection<int, AuraProperty> */
    public Collection $properties;

    /**
     * @param  iterable<int, AuraProperty>  $properties
     */
    public function __construct(
        public string $summary = '',
        public string $description = '',
        iterable $properties = [],
    ) {
        $this->properties = collect($properties);
    }
}
