<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use ReflectionEnum;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;
use UnexpectedValueException;
use UnitEnum;

class EnumHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    public readonly bool $multiple;

    /** @var class-string<UnitEnum> */
    public readonly string $enumClass;

    /** @var Collection<int, UnitEnum> */
    public readonly Collection $enumCases;

    public readonly ?string $backedBy;

    public function __construct(protected FinalAuraProperty $property, protected int $buttonLimit = 2)
    {
        $this->multiple = is_a($this->namedType()->name, Collection::class, true);

        $this->enumClass = match (true) {
            is_subclass_of($this->namedType()->name, UnitEnum::class) => $this->namedType()->name,
            is_subclass_of($enumClass = $this->namedType()->atomicParameters()->get(1)->name ?? '', UnitEnum::class) => $enumClass,
            default => throw new UnexpectedValueException("Enumeration expected, {$this->namedType()} given."),
        };

        /** @var list<UnitEnum> $include */
        $include = value($property->meta['include'] ?? $property->meta['only'] ?? $this->enumClass::cases());
        /** @var list<UnitEnum> $exclude */
        $exclude = value($property->meta['exclude'] ?? $property->meta['except'] ?? []);

        $this->enumCases = collect($include)->filter(
            fn (UnitEnum $case) => in_array($case, $this->enumClass::cases(), true) && ! in_array($case, $exclude, true)
        );

        $this->backedBy = is_subclass_of($this->enumClass, BackedEnum::class)
            ? (new ReflectionEnum($this->enumClass))->getBackingType()?->getName() : null;
    }

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect([
            UnitEnum::class, Collection::class.'<int, '.UnitEnum::class.'>',
        ])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return $this->enumCases->count() + $this->property->type->nullable > $this->buttonLimit
            ? 'formster::form.select'
            : 'formster::form.radio';
    }

    public function validationRules(): string|array
    {
        $enumRule = $this->backedBy
            ? Rule::enum($this->enumClass)
            : Rule::in(array_column($this->enumCases->all(), 'name'));

        return $this->property->mergeValidationRules(match (true) {
            $this->multiple => ['list'],
            $this->property->type->nullable => ['present', 'nullable', $enumRule],
            default => ['required', $enumRule],
        });
    }

    public function handle(object $object, Request $request): void
    {
        if ($this->property->type->nullable && $request->isNotFilled($this->property->variableName)) {
            $object->{$this->property->variableName} = null;

            return;
        }

        if ($this->multiple) {
            $object->{$this->property->variableName} = $request
                ->collect($this->property->variableName)
                ->map($this->newEnum(...)); // @phpstan-ignore argument.type
        } else {
            $object->{$this->property->variableName} = $this->newEnum(
                $this->backedBy === 'int'
                    ? $request->integer($this->property->variableName)
                    : (string) $request->string($this->property->variableName)
            );
        }
    }

    protected function newEnum(string|int $value): UnitEnum
    {
        if (is_subclass_of($this->enumClass, BackedEnum::class)) {
            /** @var BackedEnum */
            return match ($this->backedBy) {
                'int' => $this->enumClass::from((int) $value),
                'string' => $this->enumClass::from((string) $value),
                default => constant($this->enumClass.'::'.$value),
            };
        }

        /** @var UnitEnum */
        return constant($this->enumClass.'::'.$value);
    }
}
