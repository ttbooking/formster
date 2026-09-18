<?php

declare(strict_types=1);

namespace TTBooking\Formster;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use TTBooking\Formster\Concerns\HasEvents;
use TTBooking\Formster\Contracts\Comparable;
use TTBooking\Formster\Contracts\HandlerFactory;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\FinalAura;
use TTBooking\Formster\Entities\FinalAuraProperty;
use TTBooking\Formster\Events\ObjectChanged;
use TTBooking\Formster\Events\ObjectChanging;
use TTBooking\Formster\Events\PropertyChanged;
use TTBooking\Formster\Events\PropertyChanging;

use function TTBooking\Formster\Support\prop_desc;

class ActionHandler implements Contracts\ActionHandler
{
    use HasEvents;

    /**
     * The event dispatcher instance.
     */
    protected static ?Dispatcher $dispatcher;

    public function __construct(protected PropertyParser $parser, protected HandlerFactory $handler) {}

    public function update(Request $request, object $object): object
    {
        $aura = $this->parseObject($object);

        $properties = $this->getWritableProperties($object, $aura);

        $this->validateRequest($request, $object, $properties);

        $this->performUpdate($request, $object, $aura, $properties);

        return $object;
    }

    protected function parseObject(object $object): FinalAura
    {
        return $this->parser->parse($object)->finalize();
    }

    /**
     * @return Collection<string, FinalAuraProperty>
     */
    protected function getWritableProperties(object $object, FinalAura $aura): Collection
    {
        return $aura->properties->filter(
            static fn (FinalAuraProperty $property) => $property->writable && Gate::check(
                array_unique([$aura->updatePolicy, $property->updatePolicy]),
                [$object, $property->variableName]
            )
        );
    }

    /**
     * @param  iterable<FinalAuraProperty>  $properties
     */
    protected function validateRequest(Request $request, object $object, iterable $properties): void
    {
        $rules = $attributes = [];
        foreach ($properties as $property) {
            $rules[$property->variableName] = $this->handler->for($property)->validationRules();
            $attributes[$property->variableName] =
                (string) str(prop_desc($object, $property->variableName, $property->description))
                    ->inlineMarkdown()
                    ->stripTags()
                    ->squish();
        }

        $request->validate($rules, [], $attributes);
    }

    /**
     * @param  iterable<FinalAuraProperty>  $properties
     */
    protected function performUpdate(Request $request, object $object, FinalAura $aura, iterable $properties): void
    {
        if ($this->fireEvent(new ObjectChanging($object, $aura)) === false) {
            return;
        }

        $oldValues = $newValues = [];
        foreach ($properties as $property) {
            if ($this->fireEvent(new PropertyChanging($object, $aura, $property)) === false) {
                continue;
            }

            $oldValue = $object->{$property->variableName};

            $this->handler->for($property)->handle($object, $request);

            if (! static::sameAs($newValue = $object->{$property->variableName}, $oldValue)) {
                $this->fireEvent(new PropertyChanged($object, $aura, $property, $oldValue), false);
                $oldValues[$property->variableName] = $oldValue;
                $newValues[$property->variableName] = $newValue;
            }
        }

        if ($oldValues && $newValues) {
            $this->fireEvent(new ObjectChanged($object, $aura, $oldValues, $newValues), false);
        }
    }

    protected static function sameAs(mixed $value1, mixed $value2): bool
    {
        return $value1 instanceof Comparable ? $value1->sameAs($value2) : $value1 == $value2;
    }
}
