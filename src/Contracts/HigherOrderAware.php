<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

/**
 * @template TProxy of object
 */
interface HigherOrderAware
{
    /**
     * @param  TProxy  $proxy
     * @return $this
     */
    public function setProxy(object $proxy): static;
}
