<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\FinalAuraProperty;
use UnexpectedValueException;

/**
 * @template T of Model = Model
 *
 * @implements PropertyHandler<T>
 */
class RelatedModelHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return $property->type->contains(Model::class);
    }

    public function component(): string
    {
        return 'formster::form.model';
    }

    public function validationRules(): string|array
    {
        /** @var class-string<T> $modelClass */
        $modelClass = $this->namedType()->name;

        return $this->property->mergeValidationRules([
            'required',
            Rule::exists($modelClass, (new $modelClass)->getKeyName()),
        ]);
    }

    public function handle(object $object, Request $request): void
    {
        $newKey = match ($object->getKeyType()) {
            'int' => $request->integer($this->property->variableName),
            'string' => (string) $request->string($this->property->variableName),
            default => throw new UnexpectedValueException('Unsupported key type.'),
        };

        /** @var BelongsTo<Model, T> $relationship */
        $relationship = $object->{$this->property->variableName}();

        $relationship->associate($newKey);
    }
}
