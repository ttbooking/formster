<?php

declare(strict_types=1);

namespace TTBooking\Formster\Parsers;

use TTBooking\Formster\Contracts\ParserFactory;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Exceptions\ParserException;

class AggregateParser implements PropertyParser
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

            $this->parsers[$parser] ??= $resolved;
        }
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
