<?php

declare(strict_types=1);

namespace TTBooking\Formster;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Handlers\FallbackHandler;

class HandlerFactory implements Contracts\HandlerFactory
{
    /** @var Collection<class-string<PropertyHandler>, array<string, mixed>> */
    protected Collection $handlers;

    /**
     * @template TKey of int|class-string<PropertyHandler>
     *
     * @param  array<TKey, (TKey is int ? class-string<PropertyHandler> : array<string, mixed>)>  $handlers
     */
    public function __construct(protected Container $container, array $handlers)
    {
        // @phpstan-ignore-next-line
        $this->handlers = collect($handlers)->mapWithKeys(
            static fn ($value, $key) => is_int($key) ? [$value => []] : [$key => $value] // @phpstan-ignore-line
        );
    }

    public function for(AuraProperty $property): PropertyHandler
    {
        /** @var class-string<PropertyHandler> $handlerClass */
        $handlerClass = $this->handlers->keys()->first->satisfies($property) ?? FallbackHandler::class; // @phpstan-ignore-line

        $parameters = Arr::mapWithKeys(
            $this->handlers[$handlerClass] ?? [],
            static fn ($value, $key) => [Str::camel($key) => $value]
        );

        /** @var PropertyHandler */
        return $this->container->make($handlerClass, [...compact('property'), ...$parameters]);
    }
}
