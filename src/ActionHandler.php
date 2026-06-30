<?php

declare(strict_types=1);

namespace TTBooking\Formster;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use TTBooking\Formster\Contracts\HandlerFactory;
use TTBooking\Formster\Contracts\PropertyParser;

class ActionHandler implements Contracts\ActionHandler
{
    public function __construct(protected PropertyParser $parser, protected HandlerFactory $handler) {}

    public function update(Request $request, object $object): object
    {
        $aura = $this->parser->parse($object);

        foreach ($aura->properties as $property) {
            if (! policy($object) || Gate::allows(
                array_unique([$aura->updatePolicy, $property->updatePolicy]),
                [$object, $property->variableName])
            ) {
                $this->handler->for($property)->handle($object, $request);
            }
        }

        return $object;
    }
}
