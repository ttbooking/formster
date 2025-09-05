<?php

declare(strict_types=1);

namespace TTBooking\Formster;

use Illuminate\Support\Manager;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Parsers\PhpDocParser;
use TTBooking\Formster\Parsers\PhpStanParser;
use TTBooking\Formster\Parsers\ReflectionParser;

class PropertyParserManager extends Manager implements PropertyParser
{
    public function createPhpdocDriver(): PhpDocParser
    {
        return $this->container->make(PhpDocParser::class);
    }

    public function createPhpstanDriver(): PhpStanParser
    {
        return $this->container->make(PhpStanParser::class);
    }

    public function createReflectionParser(): ReflectionParser
    {
        return $this->container->make(ReflectionParser::class);
    }

    public function parser(?string $parser = null): PropertyParser
    {
        /** @var PropertyParser */
        return $this->driver($parser);
    }

    public function parse(object|string $objectOrClass): Aura
    {
        return $this->parser()->parse($objectOrClass);
    }

    public function getDefaultDriver(): string
    {
        /** @var string */
        return $this->config->get('formster.property_parser', 'phpstan');
    }
}
