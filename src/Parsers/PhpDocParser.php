<?php

declare(strict_types=1);

namespace TTBooking\Formster\Parsers;

use ArgumentCountError;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionClass;
use Throwable;
use TTBooking\Formster\Concerns\PerformsHigherOrderCalls;
use TTBooking\Formster\Contracts\HigherOrderAware;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraIntersectionType;
use TTBooking\Formster\Entities\AuraNamedType;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Entities\AuraType;
use TTBooking\Formster\Entities\AuraUnionType;

/**
 * @implements HigherOrderAware<PropertyParser>
 */
class PhpDocParser implements HigherOrderAware, PropertyParser
{
    /** @use PerformsHigherOrderCalls<PropertyParser> */
    use PerformsHigherOrderCalls;

    public function __construct()
    {
        $this->proxy = $this;
    }

    public function parse(object|string $objectOrClass): Aura
    {
        $docblock = DocBlockFactory::createInstance()->create(
            $refClass = new ReflectionClass($objectOrClass),
            (new ContextFactory)->createFromReflector($refClass)
        );

        try {
            $defaultObject = $refClass->newInstance();
        } catch (ArgumentCountError) {
            $defaultObject = $refClass->newInstanceWithoutConstructor();
        }

        /** @var IlluminateCollection<int, Property|PropertyRead|PropertyWrite> $tags */
        $tags = collect(['property', 'property-read', 'property-write'])->flatMap($docblock->getTagsByName(...));

        $props = $tags->map(function (Property|PropertyRead|PropertyWrite $property) use ($defaultObject) {
            $variableName = (string) $property->getVariableName();
            [$hasDefaultValue, $defaultValue] = $this->fetchDefaultPropertyValue($defaultObject, $variableName);

            return new AuraProperty(
                readable: ! $property instanceof PropertyWrite,
                writable: ! $property instanceof PropertyRead,
                type: $this->parseType($property->getType()),
                variableName: $variableName,
                description: (string) $property->getDescription(),
                hasDefaultValue: $hasDefaultValue,
                defaultValue: $defaultValue,
            );
        });

        return new Aura(
            summary: $docblock->getSummary(),
            description: (string) $docblock->getDescription(),
            properties: $props,
        );
    }

    protected function parseType(?Type $type): AuraType
    {
        return match (true) {
            is_null($type) => new AuraNamedType('mixed', nullable: true),
            $type instanceof Nullable => new AuraUnionType([$this->parseType($type->getActualType()), new AuraNamedType('null')]),
            $type instanceof Compound => new AuraUnionType($this->parseTypes(iterator_to_array($type, false))),
            $type instanceof Intersection => new AuraIntersectionType($this->parseTypes(iterator_to_array($type, false))),
            $type instanceof ClassString => new AuraNamedType(
                'class-string',
                (null !== $fqsen = $type->getFqsen()) ? [new AuraNamedType((string) $fqsen)] : []
            ),
            $type instanceof Collection => new AuraNamedType(
                (string) ($type->getFqsen() ?? 'object'),
                $this->parseTypes([$type->getKeyType(), $type->getValueType()]),
            ),
            $type instanceof AbstractList => new AuraNamedType(
                Str::kebab(rtrim($type::class, '_')),
                $this->parseTypes([$type->getKeyType(), $type->getValueType()]),
            ),
            default => new AuraNamedType((string) $type),
        };
    }

    /**
     * @param  list<Type>  $types
     * @return list<AuraType>
     */
    protected function parseTypes(array $types): array
    {
        return array_map(fn (Type $type) => $this->parseType($type), $types);
    }

    /**
     * @return array{bool, mixed}
     */
    protected function fetchDefaultPropertyValue(object $object, string $property): array
    {
        try {
            return $object instanceof Model
                ? [$object->hasAttribute($property), $object->getAttributeValue($property)]
                : [isset($object->$property), $object->$property];
        } catch (Throwable) {
            return [false, null];
        }
    }
}
