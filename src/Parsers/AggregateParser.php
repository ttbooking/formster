<?php

declare(strict_types=1);

namespace TTBooking\Formster\Parsers;

use TTBooking\Formster\Contracts\HigherOrderAware;
use TTBooking\Formster\Contracts\ParserFactory;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Exceptions\ParserException;

/**
 * @implements HigherOrderAware<PropertyParser>
 */
class AggregateParser implements HigherOrderAware, PropertyParser
{
    /** @var array<string, PropertyParser> */
    protected array $parsers = [];

    /**
     * @param  iterable<string>  $parsers
     */
    public function __construct(ParserFactory $factory, iterable $parsers = [])
    {
        foreach ($parsers as $parser) {
            $resolved = $factory->parser($parser);

            if ($resolved instanceof static) {
                $this->parsers = [...$this->parsers, ...$resolved->parsers];

                continue;
            }

            if ($resolved instanceof CachingParser) {
                trigger_deprecation('ttbooking/formster', '1.0',
                    'Aggregation of property parser already decorated by [%s] is deprecated.', CachingParser::class);

                $resolved = $resolved->parser;
            }

            $this->parsers[$parser] ??= $resolved instanceof HigherOrderAware
                ? (clone $resolved)->setProxy($this)
                : $resolved;
        }
    }

    public function setProxy(object $proxy): static
    {
        foreach ($this->parsers as $name => $parser) {
            if ($parser instanceof HigherOrderAware) {
                $this->parsers[$name] = (clone $parser)->setProxy($proxy);
            }
        }

        return $this;
    }

    public function parse(object|string $objectOrClass): Aura
    {
        if (! $this->parsers) {
            throw new ParserException('No suitable parsers found.');
        }

        return array_reduce(
            $this->parsers,
            static fn (Aura $aura, PropertyParser $parser) => $aura->merge($parser->parse($objectOrClass)),
            new Aura
        );
    }
}
