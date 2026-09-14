<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ReflectionEnum;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraNamedType;
use TTBooking\Formster\Entities\FinalAuraProperty;
use UnitEnum;

class EnumHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    public function __construct(protected FinalAuraProperty $property, protected int $buttonLimit = 2) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return $property->type instanceof AuraNamedType
            && is_subclass_of($property->type->name, UnitEnum::class);
    }

    public function component(): string
    {
        /** @var class-string<UnitEnum> $enumClass */
        $enumClass = $this->namedType()->name;

        return count($enumClass::cases()) + $this->property->type->nullable > $this->buttonLimit
            ? 'formster::form.select'
            : 'formster::form.radio';
    }

    public function validationRules(): string|array
    {
        /** @var class-string<UnitEnum> $enumClass */
        $enumClass = $this->namedType()->name;

        return $this->property->mergeValidationRules([
            ...($this->property->type->nullable ? ['present', 'nullable'] : ['required']),
            is_subclass_of($enumClass, BackedEnum::class)
                ? Rule::enum($enumClass)
                : Rule::in(array_column($enumClass::cases(), 'name')),
        ]);
    }

    public function handle(object $object, Request $request): void
    {
        if ($this->property->type->nullable && $request->isNotFilled($this->property->variableName)) {
            $object->{$this->property->variableName} = null;

            return;
        }

        /** @var class-string<UnitEnum> $enumClass */
        $enumClass = $this->namedType()->name;

        $backedBy = is_subclass_of($enumClass, BackedEnum::class)
            ? (new ReflectionEnum($enumClass))->getBackingType()?->getName() : null;

        $object->{$this->property->variableName} = match ($backedBy) {
            'int' => $enumClass::from($request->integer($this->property->variableName)),
            'string' => $enumClass::from((string) $request->string($this->property->variableName)),
            default => constant($enumClass.'::'.$request->string($this->property->variableName)),
        };
    }
}
