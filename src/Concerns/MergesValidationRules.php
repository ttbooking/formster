<?php

declare(strict_types=1);

namespace TTBooking\Formster\Concerns;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Stringable;

/**
 * @phpstan-type RuleList list<void|string|Stringable|Rule|InvokableRule|ValidationRule>
 */
trait MergesValidationRules
{
    /**
     * Merge property validation rules using ellipsis (...) notation.
     *
     * @param  string|RuleList|Closure(): (string|RuleList)  $rules
     * @return string|RuleList
     */
    public function mergeValidationRules(string|array|Closure $rules = []): string|array
    {
        // Return passed rules if there are no property rules defined
        if (! $propertyRules = (array) (value($this->validationRules) ?: [])) {
            return value($rules);
        }

        // Return property rules intact unless insertion directive found
        if (false === $key = array_search('...', $propertyRules, true)) {
            return $propertyRules;
        }

        // Substitute insertion directive with passed rules
        $propertyRules[$key] = value($rules);

        /** @var RuleList */
        return collect($propertyRules)
            ->flatten()
            ->map(static fn (mixed $rule) => is_string($rule) ? explode('|', $rule) : $rule)
            ->flatten()
            ->unique()
            ->reject('...')
            ->all();
    }
}
