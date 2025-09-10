<?php

declare(strict_types=1);

namespace TTBooking\Formster\Concerns;

use TTBooking\Formster\Contracts\HigherOrderAware;

/**
 * @template TProxy of object
 *
 * @phpstan-require-implements HigherOrderAware<TProxy>
 */
trait PerformsHigherOrderCalls
{
    /** @var TProxy */
    protected object $proxy;

    public function setProxy(object $proxy): static
    {
        $this->proxy = $proxy;

        return $this;
    }
}
