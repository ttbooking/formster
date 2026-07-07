<?php

declare(strict_types=1);

namespace TTBooking\Formster\Facades;

use Illuminate\Support\Facades\Facade;
use TTBooking\Formster\Contracts\PropertyHandler as PropertyHandlerContract;
use TTBooking\Formster\Entities\FinalAuraProperty;
use TTBooking\Formster\HandlerFactory;

/**
 * @method static PropertyHandlerContract for(FinalAuraProperty $property)
 *
 * @see HandlerFactory
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
