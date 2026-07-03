<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Stringable;
use TTBooking\Formster\Entities\AuraProperty;

interface PropertyHandler
{
    public static function satisfies(AuraProperty $property): bool;

    public function component(): string;

    /**
     * @return string|list<string|Stringable|Rule|InvokableRule|ValidationRule>
     */
    public function validationRules(): string|array;

    public function handle(object $object, Request $request): void;
}
