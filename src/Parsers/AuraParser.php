<?php

declare(strict_types=1);

namespace TTBooking\Formster\Parsers;

use ReflectionClass;
use ReflectionProperty;
use TTBooking\Formster\Concerns\PerformsHigherOrderCalls;
use TTBooking\Formster\Contracts\HigherOrderAware;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraProperty;

/**
 * @implements HigherOrderAware<PropertyParser>
 */
class AuraParser implements HigherOrderAware, PropertyParser
{
    /** @use PerformsHigherOrderCalls<PropertyParser> */
    use PerformsHigherOrderCalls;

    public function __construct()
    {
        $this->proxy = $this;
    }

    public function parse(object|string $objectOrClass): Aura
    {
        $refClass = new ReflectionClass($objectOrClass);

        do {
            $aura = isset($aura) ? $this->parseClass($refClass)->merge($aura) : $this->parseClass($refClass);
        } while (false !== $refClass = $refClass->getParentClass());

        return $aura;
    }

    /**
     * @param  ReflectionClass<object>  $refClass
     */
    protected function parseClass(ReflectionClass $refClass): Aura
    {
        $aura = ($refClass->getAttributes(Aura::class)[0] ?? null)?->newInstance() ?? new Aura;

        $properties = [];
        $refProps = $refClass->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($refProps as $refProp) {
            if ($refPropAttr = $refProp->getAttributes(AuraProperty::class)[0] ?? null) {
                $properties[$refProp->name] = $refPropAttr->newInstance();
            }
        }

        return $properties ? $aura->merge(new Aura(properties: $properties)) : $aura;
    }
}
