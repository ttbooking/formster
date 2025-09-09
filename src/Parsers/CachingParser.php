<?php

declare(strict_types=1);

namespace TTBooking\Formster\Parsers;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;

class CachingParser implements PropertyParser
{
    public readonly PropertyParser $parser;

    public function __construct(
        PropertyParser $parser,
        protected Repository $cache,
        protected DateTimeInterface|DateInterval|Closure|int|null $ttl = null,
    ) {
        $this->parser = $parser instanceof static ? $parser->parser : $parser;
    }

    public function parse(object|string $objectOrClass): Aura
    {
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass;

        return $this->cache->remember(
            'formster:properties:'.$class,
            $this->ttl,
            fn () => $this->parser->parse($objectOrClass)
        );
    }
}
