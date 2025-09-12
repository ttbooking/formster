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
    protected function createDriver($driver): CachingParser
    {
        return $this->container->make(CachingParser::class, [
            'parser' => parent::createDriver($driver),
            'key' => $driver,
        ]);
    }

    protected function createAggregateDriver(): AggregateParser
    {
        /** @var string $parsers */
        $parsers = $this->config->get('formster.property_parser', '');

        return new AggregateParser($this, array_filter(
            explode(',', $parsers),
            static fn ($parser) => $parser !== '' && $parser !== 'aggregate'
        ));
    }

    protected function createPhpdocDriver(): PhpDocParser
    {
        return new PhpDocParser;
    }

    protected function createPhpstanDriver(): PhpStanParser
    {
        return new PhpStanParser;
    }

    protected function createReflectionDriver(): ReflectionParser
    {
        return new ReflectionParser;
    }
}
