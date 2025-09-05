<?php

declare(strict_types=1);

namespace TTBooking\Formster\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static object update(Request $request, object $object)
 *
 * @see \TTBooking\Formster\ActionHandler
 */
class ActionHandler extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'action-handler';
    }
}
