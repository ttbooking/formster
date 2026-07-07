<?php

declare(strict_types=1);

namespace TTBooking\Formster\Concerns;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Stringable;

trait MergesValidationRules
{
    /**
     * Merge property validation rules using ellipsis (...) notation.
     *
     * @param  string|list<string|Stringable|Rule|InvokableRule|ValidationRule>  $rules
     * @return string|list<string|Stringable|Rule|InvokableRule|ValidationRule>
     */
    public function mergeValidationRules(string|array $rules = []): string|array
    {
        // Return passed rules if there are no property rules defined
        if (! $propertyRules = (array) ($this->validationRules ?: [])) {
            return $rules;
        }

        // Return property rules intact unless insertion directive found
        if (false === $key = array_search('...', $propertyRules, true)) {
            return $propertyRules;
        }

        // Substitute insertion directive with passed rules
        $propertyRules[$key] = $rules;

        /** @var list<string|Stringable|Rule|InvokableRule|ValidationRule> */
        return collect($propertyRules)
            ->flatten()
            ->map(static fn (mixed $rule) => is_string($rule) ? explode('|', $rule) : $rule)
            ->flatten()
            ->unique()
            ->reject('...')
            ->all();
    }
}
