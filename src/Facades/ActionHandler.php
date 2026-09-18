<?php

declare(strict_types=1);

namespace TTBooking\Formster\Facades;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static object update(Request $request, object $object)
 * @method static Dispatcher|null getEventDispatcher()
 * @method static void setEventDispatcher(Dispatcher $dispatcher)
 * @method static void unsetEventDispatcher()
 * @method static mixed withoutEvents(callable $callback)
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
