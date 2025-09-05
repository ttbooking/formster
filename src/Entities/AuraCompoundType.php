<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Illuminate\Support\Collection;

abstract readonly class AuraCompoundType extends AuraType
{
    /** @var Collection<int, AuraType> */
    public Collection $types;

    /**
     * @param  iterable<int, AuraType>  $types
     */
    public function __construct(
        iterable $types,
        public string $junction = '|',
        bool $nullable = false,
    ) {
        $this->types = collect($types);
        parent::__construct($nullable);
    }

    public function contains(string $type): bool
    {
        if ($type === (string) $this) {
            return true;
        }

        $method = $this->junction === '|' ? 'contains' : 'every';

        return $this->types->$method(static fn (AuraType $auraType) => $auraType->contains($type));
    }

    public function __toString(): string
    {
        return $this->types
            ->map(static fn (AuraType $type) => $type instanceof static ? "($type)" : $type)
            ->implode($this->junction);
    }
}
