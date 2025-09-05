<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Stringable;

abstract readonly class AuraType implements Stringable
{
    public function __construct(public bool $nullable = false) {}

    abstract public function contains(string $type): bool;
}
