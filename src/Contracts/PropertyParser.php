<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

use TTBooking\Formster\Entities\Aura;

interface PropertyParser
{
    /**
     * @param  object|class-string  $objectOrClass
     */
    public function parse(object|string $objectOrClass): Aura;
}
