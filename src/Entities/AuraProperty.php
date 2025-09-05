<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Stringable;
use UnexpectedValueException;

readonly class AuraProperty implements Stringable
{
    public function __construct(
        public bool $readable,
        public bool $writable,
        public AuraType $type,
        public string $variableName,
        public string $description,
        public bool $hasDefaultValue = false,
        public mixed $defaultValue = null,
    ) {}

    public function __toString(): string
    {
        $tag = match (true) {
            $this->readable && $this->writable => '@property',
            $this->readable => '@property-read',
            $this->writable => '@property-write',
            default => throw new UnexpectedValueException('Property cannot be both non-readable and non-writable.'),
        };

        return rtrim(sprintf('%s %s $%s %s', $tag, $this->type, $this->variableName, $this->description));
    }
}
