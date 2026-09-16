<?php

declare(strict_types=1);

namespace TTBooking\Formster\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use TTBooking\Formster\Entities\FinalAuraProperty;

final readonly class PropertyChanged implements ShouldDispatchAfterCommit
{
    public mixed $newValue;

    public function __construct(public object $object, public FinalAuraProperty $property, public mixed $oldValue)
    {
        $this->newValue = $object->{$property->variableName};
    }
}
