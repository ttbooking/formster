<?php

declare(strict_types=1);

namespace TTBooking\Formster\Events;

use TTBooking\Formster\Entities\FinalAura;

final readonly class ObjectChanging
{
    public function __construct(
        public object $object,
        public FinalAura $aura,
    ) {}
}
