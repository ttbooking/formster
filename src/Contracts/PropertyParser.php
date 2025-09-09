<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Exceptions\ParserException;

interface PropertyParser
{
    /**
     * @param  object|class-string  $objectOrClass
     *
     * @throws ParserException
     */
    public function parse(object|string $objectOrClass): Aura;
}
