<?php

declare(strict_types=1);

namespace TTBooking\Formster\Parsers;

use ArgumentCountError;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
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
use TTBooking\Formster\Exceptions\ParserException;

/**
 * @implements HigherOrderAware<PropertyParser>
 */
class PhpStanParser implements HigherOrderAware, PropertyParser
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

        try {
            $defaultObject = $refClass->newInstance();
        } catch (ArgumentCountError) {
            $defaultObject = $refClass->newInstanceWithoutConstructor();
        }

        if (! $docComment = $refClass->getDocComment()) {
            return new Aura;
        }

        $config = new ParserConfig([]);
        $lexer = new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);
        $phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);

        $tokens = new TokenIterator($lexer->tokenize($docComment));
        $phpDocNode = $phpDocParser->parse($tokens);

        $context = (new ContextFactory)->createFromReflector($refClass);
        $typeResolver = new TypeResolver;
        $resolver = static fn (string $type) => ltrim((string) $typeResolver->resolve($type, $context), '\\');

        $props = [];
        foreach (['@property', '@property-read', '@property-write'] as $tag) {
            foreach ($phpDocNode->getPropertyTagValues($tag) as $node) {
                $variableName = ltrim($node->propertyName, '$');
                [$hasDefaultValue, $defaultValue] = $this->fetchDefaultPropertyValue($defaultObject, $variableName);

                $props[] = new AuraProperty(
                    readable: $tag !== '@property-write',
                    writable: $tag !== '@property-read',
                    type: $this->parseType($node->type, $resolver),
                    variableName: $variableName,
                    description: $node->description,
                    hasDefaultValue: $hasDefaultValue,
                    defaultValue: $defaultValue,
                );
            }
        }

        $comment = (string) Arr::first($phpDocNode->children, static fn (PhpDocChildNode $child) => $child instanceof PhpDocTextNode);
        [$summary, $description] = (preg_split('~\R\R~u', $comment, 2, PREG_SPLIT_NO_EMPTY) ?: []) + ['', ''];

        return new Aura(
            summary: $summary,
            description: $description,
            properties: $props,
        );
    }

    /**
     * @param  null|Closure(string): string  $typeResolver
     *
     * @throws ParserException
     */
    protected function parseType(TypeNode $type, ?Closure $typeResolver = null): AuraType
    {
        $typeResolver ??= static fn (string $type) => $type;

        return match (true) {
            $type instanceof UnionTypeNode => new AuraUnionType($this->parseTypes($type->types, $typeResolver)),
            $type instanceof IntersectionTypeNode => new AuraIntersectionType($this->parseTypes($type->types, $typeResolver)),
            $type instanceof IdentifierTypeNode => new AuraNamedType(
                $resolved = $typeResolver($type->name),
                aura: $this->parseNested($resolved)
            ),
            $type instanceof GenericTypeNode => new AuraNamedType(
                $resolved = $typeResolver($type->type->name),
                $this->parseTypes($type->genericTypes, $typeResolver),
                aura: $this->parseNested($resolved)
            ),
            $type instanceof ConstTypeNode => new AuraNamedType((string) $type->constExpr),
            default => throw new ParserException('Unsupported node type.'),
        };
    }

    /**
     * @param  array<TypeNode>  $types
     * @param  null|Closure(string): string  $typeResolver
     * @return list<AuraType>
     *
     * @throws ParserException
     */
    protected function parseTypes(array $types, ?Closure $typeResolver = null): array
    {
        return array_map(
            fn (TypeNode $type) => $this->parseType($type, $typeResolver),
            array_values($types)
        );
    }

    protected function parseNested(string $class): ?Aura
    {
        return (class_exists($class) || interface_exists($class)) && ! enum_exists($class)
            ? $this->proxy->parse($class) : null;
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
