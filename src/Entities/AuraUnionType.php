<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

readonly class AuraUnionType extends AuraCompoundType
{
    /**
     * @param  iterable<int, AuraType>  $types
     */
    final public function __construct(iterable $types, bool $nullable = false)
    {
        parent::__construct($types, '|', $nullable);
    }
}
