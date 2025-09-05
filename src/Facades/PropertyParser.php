<?php

declare(strict_types=1);

namespace TTBooking\Formster\Facades;

use Illuminate\Support\Facades\Facade;
use TTBooking\Formster\Contracts\PropertyParser as PropertyParserContract;
use TTBooking\Formster\Entities\Aura;

/**
 * @method static PropertyParserContract parser(string|null $parser = null)
 * @method static Aura parse(object|string $objectOrClass)
 *
 * @see \TTBooking\Formster\PropertyParserManager
 */
class PropertyParser extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'property-parser';
    }
}
