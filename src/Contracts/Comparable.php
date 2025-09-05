<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

interface Comparable
{
    public function sameAs(mixed $that): bool;
}
