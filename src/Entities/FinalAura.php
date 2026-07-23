<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Illuminate\Support\Collection;

readonly class FinalAura
{
    /**
     * @param  Collection<string, FinalAuraProperty>  $properties
     * @param  array<string, mixed>  $meta
     */
    final public function __construct(
        public string $summary,
        public string $description,
        public Collection $properties,
        public array $meta = [],
        public string $viewPolicy = 'view',
        public string $updatePolicy = 'update',
    ) {}
}
