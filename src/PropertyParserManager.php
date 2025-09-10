<?php

declare(strict_types=1);

namespace TTBooking\Formster;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Manager;
use TTBooking\Formster\Contracts\ParserFactory;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Parsers\AggregateParser;
use TTBooking\Formster\Parsers\CachingParser;
use TTBooking\Formster\Parsers\PhpDocParser;
use TTBooking\Formster\Parsers\PhpStanParser;
use TTBooking\Formster\Parsers\ReflectionParser;

class PropertyParserManager extends Manager implements ParserFactory, PropertyParser
{
    public function parser(?string $parser = null): PropertyParser
    {
        /** @var PropertyParser */
        return $this->driver($parser);
    }

    public function parse(object|string $objectOrClass): Aura
    {
        return $this->parser()->parse($objectOrClass);
    }

    public function getDefaultDriver(): ?string
    {
        /** @var string|null $parser */
        $parser = $this->config->get('formster.property_parser');

        return is_string($parser) && str_contains($parser, ',') ? 'aggregate' : $parser;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createAggregateDriver(): CachingParser
    {
        /** @var string $parsers */
        $parsers = $this->config->get('formster.property_parser', '');

        return $this->decorateInstance(
            new AggregateParser($this, array_filter(
                explode(',', $parsers),
                static fn ($parser) => $parser !== '' && $parser !== 'aggregate'
            )),
            'aggregate'
        );
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createPhpdocDriver(): CachingParser
    {
        return $this->decorateInstance(new PhpDocParser, 'phpdoc');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createPhpstanDriver(): CachingParser
    {
        return $this->decorateInstance(new PhpStanParser, 'phpstan');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createReflectionDriver(): CachingParser
    {
        return $this->decorateInstance(new ReflectionParser, 'reflection');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function decorateInstance(PropertyParser $parser, ?string $key = null): CachingParser
    {
        return $this->container->make(CachingParser::class, compact('parser', 'key'));
    }
}
