<?php

declare(strict_types=1);

namespace TTBooking\Formster\Concerns;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Stringable;
use TTBooking\Formster\Contracts\PropertyHandler;

/**
 * @phpstan-require-implements PropertyHandler
 */
trait MergesValidationRules
{
    public function validationRules(): string|array
    {
        return $this->mergeValidationRules();
    }

    /**
     * Merge handler and property validation rules using ellipsis (...) notation.
     *
     * @param  string|list<string|Stringable|Rule|InvokableRule|ValidationRule>  $rules
     * @return string|list<string|Stringable|Rule|InvokableRule|ValidationRule>
     */
    protected function mergeValidationRules(string|array $rules = []): string|array
    {
        // Return handler rules if there are no property rules defined
        if (! $propertyRules = (array) ($this->property->validationRules ?: [])) {
            return $rules;
        }

        // Return property rules intact unless insertion directive found
        if (false === $key = array_search('...', $propertyRules, true)) {
            return $propertyRules;
        }

        // Substitute insertion directive with handler rules
        $propertyRules[$key] = $rules;

        /** @var list<string|Stringable|Rule|InvokableRule|ValidationRule> */
        return Arr::flatten($propertyRules);
    }
}
