<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Closure;
use TTBooking\Formster\Concerns\MergesValidationRules;

/**
 * @phpstan-import-type RuleList from MergesValidationRules
 */
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
        /** @var string|RuleList|Closure(): (string|RuleList) */
        public string|array|Closure $validationRules = [],
        public string $viewPolicy = 'view',
        public string $updatePolicy = 'update',
    ) {}
}
