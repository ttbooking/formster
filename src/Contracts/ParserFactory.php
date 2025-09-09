<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

use InvalidArgumentException;

interface ParserFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public function parser(?string $parser = null): PropertyParser;
}
