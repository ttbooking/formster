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
use UnitEnum;

class EnumHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    public function __construct(protected FinalAuraProperty $property, protected int $buttonLimit = 2) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect([
            UnitEnum::class, Collection::class.'<int, '.UnitEnum::class.'>',
        ])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        $enumClass = $this->extractEnumClass();

        return count($enumClass::cases()) + $this->property->type->nullable > $this->buttonLimit
            ? 'formster::form.select'
            : 'formster::form.radio';
    }

    public function validationRules(): string|array
    {
        $enumClass = $this->extractEnumClass();

        return $this->property->mergeValidationRules([
            ...($this->property->type->nullable ? ['present', 'nullable'] : ['required']),
            $this->namedType()->name === Collection::class ? 'list' : (
                is_subclass_of($enumClass, BackedEnum::class)
                    ? Rule::enum($enumClass)
                    : Rule::in(array_column($enumClass::cases(), 'name'))
            ),
        ]);
    }

    public function handle(object $object, Request $request): void
    {
        if ($this->property->type->nullable && $request->isNotFilled($this->property->variableName)) {
            $object->{$this->property->variableName} = null;

            return;
        }

        $enumClass = $this->extractEnumClass();

        $backedBy = is_subclass_of($enumClass, BackedEnum::class)
            ? (new ReflectionEnum($enumClass))->getBackingType()?->getName() : null;

        if ($this->namedType()->name === Collection::class) {
            $object->{$this->property->variableName} = $request
                ->collect($this->property->variableName)
                // @phpstan-ignore argument.type
                ->map(static fn (int|string $value) => match ($backedBy) {
                    'int' => $enumClass::from((int) $value),
                    'string' => $enumClass::from((string) $value),
                    default => constant($enumClass.'::'.$value),
                });

            return;
        }

        $object->{$this->property->variableName} = match ($backedBy) {
            'int' => $enumClass::from($request->integer($this->property->variableName)),
            'string' => $enumClass::from((string) $request->string($this->property->variableName)),
            default => constant($enumClass.'::'.$request->string($this->property->variableName)),
        };
    }

    /**
     * @return class-string<UnitEnum>
     */
    public function extractEnumClass(): string
    {
        $type = $this->namedType();

        /** @var class-string<UnitEnum> */
        return match (true) {
            is_subclass_of($type->name, UnitEnum::class) => $type->name,
            is_subclass_of($enumClass = $type->atomicParameters()->get(1)->name ?? '', UnitEnum::class) => $enumClass,
            default => '',
        };
    }
}
