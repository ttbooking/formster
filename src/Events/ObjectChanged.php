<?php

declare(strict_types=1);

namespace TTBooking\Formster\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use TTBooking\Formster\Entities\FinalAura;

final readonly class ObjectChanged implements ShouldDispatchAfterCommit
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function __construct(
        public object $object,
        public FinalAura $aura,
        public array $oldValues,
        public array $newValues,
    ) {}
}
