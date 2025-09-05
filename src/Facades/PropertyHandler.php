<?php

declare(strict_types=1);

namespace TTBooking\Formster\Facades;

use Illuminate\Support\Facades\Facade;
use TTBooking\Formster\Contracts\PropertyHandler as PropertyHandlerContract;
use TTBooking\Formster\Entities\AuraProperty;

/**
 * @method static PropertyHandlerContract for(AuraProperty $property)
 *
 * @see \TTBooking\Formster\HandlerFactory
 */
class PropertyHandler extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'property-handler';
    }
}
