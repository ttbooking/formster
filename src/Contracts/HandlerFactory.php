<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

use InvalidArgumentException;
use TTBooking\Formster\Entities\FinalAuraProperty;

interface HandlerFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public function for(FinalAuraProperty $property): PropertyHandler;
}
