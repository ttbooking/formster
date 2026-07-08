<?php

declare(strict_types=1);

namespace TTBooking\Formster\Contracts;

use Illuminate\Http\Request;
use TTBooking\Formster\Concerns\MergesValidationRules;
use TTBooking\Formster\Entities\FinalAuraProperty;

/**
 * @phpstan-import-type RuleList from MergesValidationRules
 */
interface PropertyHandler
{
    public static function satisfies(FinalAuraProperty $property): bool;

    public function component(): string;

    /**
     * @return string|RuleList
     */
    public function validationRules(): string|array;

    public function handle(object $object, Request $request): void;
}
