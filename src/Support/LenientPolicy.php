<?php

declare(strict_types=1);

namespace TTBooking\Formster\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Reflector;
use TTBooking\Formster\Entities\Aura;

class LenientPolicy
{
    /**
     * @param  list<mixed>  $arguments
     */
    public function __invoke(?Authenticatable $user, string $ability, array $arguments): ?bool
    {
        // Continue standard flow if there's no subject
        if (! is_object($arguments[0] ?? null)) {
            return null;
        }

        // Continue standard flow if subject is not marked with Aura attribute
        if (! Reflector::getClassAttribute($arguments[0], Aura::class, true)) {
            return null;
        }

        // Grant full access if policy class not found
        if (! $policy = policy($arguments[0])) {
            return true;
        }

        // Grant full access if policy method not found
        /** @var object $policy */
        if (! method_exists($policy, $ability)) {
            return true;
        }

        return null;
    }
}
