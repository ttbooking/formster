<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

use Illuminate\Http\Request;
use TTBooking\Formster\Entities\AuraProperty;

interface PropertyHandler
{
    public static function satisfies(AuraProperty $property): bool;

    public function component(): string;

    public function handle(object $object, Request $request): void;

    public function validate(Request $request): bool;
}
