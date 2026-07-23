<?php

declare(strict_types=1);

namespace TTBooking\Formster\Entities;

use Attribute;
use Closure;
use Illuminate\Support\Traits\Conditionable;
use Stringable;
use TTBooking\Formster\Concerns\MergesValidationRules;
use TypeError;
use UnexpectedValueException;

/**
 * @phpstan-import-type RuleList from MergesValidationRules
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class AuraProperty implements Stringable
{
    use Conditionable, MergesValidationRules;

    /**
     * @param  string|RuleList|Closure(): (string|RuleList)  $validationRules
     * @param  array<string, mixed>  $meta
     */
    final public function __construct(
        public readonly ?bool $readable = true,
        public readonly ?bool $writable = true,
        public readonly ?AuraType $type = null,
        public ?string $variableName = null,
        public readonly ?string $description = null,
        public readonly ?bool $hasDefaultValue = false,
        public readonly mixed $defaultValue = null,
        public string|array|Closure $validationRules = [],
        public array $meta = [],
        public readonly ?string $viewPolicy = 'view',
        public readonly ?string $updatePolicy = 'update',
    ) {}

    /**
     * @throws TypeError
     */
    public function finalize(): FinalAuraProperty
    {
        return new FinalAuraProperty(
            readable: $this->readable,
            writable: $this->writable,
            type: $this->type,
            variableName: $this->variableName,
            description: $this->description,
            hasDefaultValue: $this->hasDefaultValue,
            defaultValue: $this->defaultValue,
            validationRules: $this->validationRules,
            meta: $this->meta,
            viewPolicy: $this->viewPolicy,
            updatePolicy: $this->updatePolicy,
        );
    }

    public function merge(self $property): static
    {
        return new static(
            readable: $property->readable ?? $this->readable,
            writable: $property->writable ?? $this->writable,
            type: $property->type ?? $this->type,
            variableName: $property->variableName ?? $this->variableName,
            description: $property->description ?? $this->description,
            hasDefaultValue: $property->hasDefaultValue ?? $this->hasDefaultValue,
            defaultValue: $property->defaultValue ?? $this->defaultValue,
            validationRules: $property->mergeValidationRules($this->validationRules),
            meta: $property->meta + $this->meta,
            viewPolicy: $property->viewPolicy ?? $this->viewPolicy,
            updatePolicy: $property->updatePolicy ?? $this->updatePolicy,
        );
    }

    public function __toString(): string
    {
        $tag = match (true) {
            $this->readable && $this->writable => '@property',
            $this->readable => '@property-read',
            $this->writable => '@property-write',
            default => throw new UnexpectedValueException('Property cannot be both non-readable and non-writable.'),
        };

        return rtrim(sprintf('%s %s $%s %s', $tag, $this->type, $this->variableName, $this->description));
    }
}
