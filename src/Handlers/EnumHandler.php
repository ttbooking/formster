<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use BackedEnum;
use Illuminate\Http\Request;
use ReflectionEnum;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraNamedType;
use TTBooking\Formster\Entities\AuraProperty;

class EnumHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    public function __construct(public AuraProperty $property, protected int $buttonLimit = 2) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return $property->type instanceof AuraNamedType
            && is_subclass_of($property->type->name, BackedEnum::class);
    }

    public function component(): string
    {
        /** @var class-string<BackedEnum> $enumClass */
        $enumClass = $this->namedType()->name;

        return count($enumClass::cases()) > $this->buttonLimit
            ? 'formster::form.select'
            : 'formster::form.radio';
    }

    public function handle(object $object, Request $request): void
    {
        /** @var class-string<BackedEnum> $enumClass */
        $enumClass = $this->namedType()->name;

        $intBacked = (new ReflectionEnum($enumClass))->getBackingType()?->getName() === 'int';

        $object->{$this->property->variableName} = $intBacked
            ? $enumClass::from($request->integer($this->property->variableName))
            : $enumClass::from((string) $request->string($this->property->variableName));
    }

    public function validate(Request $request): bool
    {
        return true;
    }
}
