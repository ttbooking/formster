<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Illuminate\Support\Collection;

readonly class AuraNamedType extends AuraType
{
    /** @var Collection<int, AuraType> */
    public Collection $parameters;

    /**
     * @param  iterable<int, AuraType>  $parameters
     */
    final public function __construct(
        public string $name,
        iterable $parameters = [],
        bool $nullable = false,
    ) {
        $this->parameters = collect($parameters);
        parent::__construct($nullable);
    }

    public function contains(string $type): bool
    {
        if (class_exists($this->name) || interface_exists($this->name)) {
            return is_a($this->name, $type, true);
        }

        return $type === $this->name || $type === (string) $this;
    }

    /**
     * @return Collection<int, static>
     */
    public function atomicParameters(): Collection
    {
        return $this->parameters->filter(static fn (AuraType $type) => $type instanceof static);
    }

    public function asConstExpr(): mixed
    {
        return eval('return '.$this->name.';');
    }

    public function __toString(): string
    {
        return $this->parameters->isNotEmpty()
            ? sprintf('%s<%s>', $this->name, $this->parameters->implode(', '))
            : $this->name;
    }
}
