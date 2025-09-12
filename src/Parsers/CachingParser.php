<?php

declare(strict_types=1);

namespace TTBooking\Formster\Parsers;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository;
use TTBooking\Formster\Contracts\HigherOrderAware;
use TTBooking\Formster\Contracts\PropertyParser;
use TTBooking\Formster\Entities\Aura;

/**
 * @template TPropertyParser of PropertyParser = PropertyParser
 */
class CachingParser implements PropertyParser
{
    /** @var TPropertyParser */
    public readonly PropertyParser $parser;

    protected string $key;

    /**
     * @param  TPropertyParser|static<TPropertyParser>  $parser
     */
    public function __construct(
        PropertyParser $parser,
        protected Repository $cache,
        ?string $key = null,
        protected DateTimeInterface|DateInterval|Closure|int|null $ttl = null,
    ) {
        $resolved = $parser instanceof static ? $parser->parser : $parser;
        $this->parser = $resolved instanceof HigherOrderAware ? (clone $resolved)->setProxy($this) : $resolved;

        $this->key = $key ?? get_class($this->parser);
    }

    public function parse(object|string $objectOrClass): Aura
    {
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass;

        return $this->cache->remember(
            "formster:properties:$this->key:$class",
            $this->ttl,
            fn () => $this->parser->parse($objectOrClass)
        );
    }
}
