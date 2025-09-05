<?php

declare(strict_types=1);

namespace TTBooking\Formster\Concerns;

use TTBooking\Formster\Entities\AuraNamedType;
use UnexpectedValueException;

trait AssertsPropertyTypes
{
    public function namedType(): AuraNamedType
    {
        if ($this->property->type instanceof AuraNamedType) {
            return $this->property->type;
        }

        throw new UnexpectedValueException('Compound property types are not supported at the moment.');
    }
}
