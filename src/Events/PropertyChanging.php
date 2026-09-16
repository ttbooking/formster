<?php

declare(strict_types=1);

namespace TTBooking\Formster\Events;

use TTBooking\Formster\Entities\FinalAuraProperty;

final readonly class PropertyChanging
{
    public mixed $oldValue;

    public function __construct(public object $object, public FinalAuraProperty $property)
    {
        $this->oldValue = $object->{$property->variableName};
    }
}
