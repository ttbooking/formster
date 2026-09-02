<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;

class IntegerHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return collect([
            'int', 'integer', 'positive-int', 'negative-int', 'non-positive-int', 'non-negative-int',
        ])->contains($property->type->contains(...));
    }

    public function component(): string
    {
        return 'formster::form.number';
    }

    public function validationRules(): string|array
    {
        [$min, $max] = $this->getBounds();

        $minRule = isset($min) ? "|min:$min" : '';
        $maxRule = isset($max) ? "|max:$max" : '';

        return $this->property->mergeValidationRules('required|integer'.$minRule.$maxRule);
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = $request->integer($this->property->variableName);
    }

    /**
     * @return array{int|null, int|null}
     */
    public function getBounds(): array
    {
        /** @var array{int|null, int|null} */
        return match ($this->namedType()->name) {
            'positive-int' => [1, null],
            'negative-int' => [null, -1],
            'non-positive-int' => [null, 0],
            'non-negative-int' => [0, null],
            default => [
                $this->namedType()->atomicParameters()->get(0)?->asConstExpr(),
                $this->namedType()->atomicParameters()->get(1)?->asConstExpr(),
            ],
        };
    }
}
