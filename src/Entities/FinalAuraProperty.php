<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Stringable;
use TTBooking\Formster\Concerns\MergesValidationRules;

readonly class FinalAuraProperty
{
    use MergesValidationRules;

    final public function __construct(
        public bool $readable,
        public bool $writable,
        public AuraType $type,
        public string $variableName,
        public string $description,
        public bool $hasDefaultValue = false,
        public mixed $defaultValue = null,
        /** @var string|list<string|Stringable|Rule|InvokableRule|ValidationRule> */
        public string|array $validationRules = [],
        public string $viewPolicy = 'view',
        public string $updatePolicy = 'update',
    ) {}
}
