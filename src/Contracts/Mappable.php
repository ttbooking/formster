<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

interface Mappable
{
    /**
     * @param  array<string, mixed>  $array
     */
    public static function fromArray(array $array): static;
}
