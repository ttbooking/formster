<?php

declare(strict_types=1);

namespace TTBooking\Formster;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use TTBooking\Formster\Contracts\HandlerFactory;
use TTBooking\Formster\Contracts\PropertyParser;

use function TTBooking\Formster\Support\prop_desc;

class ActionHandler implements Contracts\ActionHandler
{
    public function __construct(protected PropertyParser $parser, protected HandlerFactory $handler) {}

    public function update(Request $request, object $object): object
    {
        $aura = $this->parser->parse($object)->finalize();

        $rules = $attributes = [];
        foreach ($aura->properties as $property) {
            $rules[$property->variableName] = $this->handler->for($property)->validationRules();
            $attributes[$property->variableName] = prop_desc($object, $property->variableName, $property->description);
        }

        $request->validate($rules, [], $attributes);

        foreach ($aura->properties as $property) {
            if ($property->writable && Gate::check(
                array_unique([$aura->updatePolicy, $property->updatePolicy]),
                [$object, $property->variableName]
            )) {
                $this->handler->for($property)->handle($object, $request);
            }
        }

        return $object;
    }
}
