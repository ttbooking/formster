<?php

declare(strict_types=1);

namespace TTBooking\Formster\Concerns;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\NullDispatcher;

trait HasEvents
{
    /**
     * Fire the given event.
     */
    protected function fireEvent(object $event, bool $halt = true): mixed
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        return static::$dispatcher->dispatch($event, halt: $halt);
    }

    /**
     * Get the event dispatcher instance.
     */
    public static function getEventDispatcher(): ?Dispatcher
    {
        return static::$dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     */
    public static function setEventDispatcher(Dispatcher $dispatcher): void
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Unset the event dispatcher instance.
     */
    public static function unsetEventDispatcher(): void
    {
        static::$dispatcher = null;
    }

    /**
     * Execute a callback without firing any events.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function withoutEvents(callable $callback): mixed
    {
        $dispatcher = static::getEventDispatcher();

        if ($dispatcher) {
            static::setEventDispatcher(new NullDispatcher($dispatcher));
        }

        try {
            return $callback();
        } finally {
            if ($dispatcher) {
                static::setEventDispatcher($dispatcher);
            }
        }
    }
}
