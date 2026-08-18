<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use InvalidArgumentException;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Entities\FinalAuraProperty;

class Model extends Component
{
    use AssertsPropertyTypes;

    public string $titleColumn;

    /** @var list<mixed> */
    public array $remainingParameters;

    /** @var Collection<array-key, mixed> */
    public Collection $options;

    public int|string|null $value;

    /**
     * Create a new component instance.
     */
    public function __construct(public FinalAuraProperty $property, EloquentModel|int|string|null $value = null)
    {
        /** @var class-string<EloquentModel> $modelClass */
        $modelClass = $this->namedType()->name;
        $this->titleColumn = $this->namedType()->atomicParameters()->get(0)?->asConstExpr() ?? 'name'; // @phpstan-ignore assign.propertyType
        $scopeName = $this->namedType()->atomicParameters()->get(1)?->asConstExpr() ?? null;
        $scopeParameters = Arr::wrap($this->namedType()->atomicParameters()->get(2)?->asConstExpr() ?? []);
        $this->remainingParameters = $this->namedType()->atomicParameters()->slice(3)->values() // @phpstan-ignore assign.propertyType
            ->map->asConstExpr()->all();

        $this->options = $modelClass::query()
            ->when($scopeName, static function ($model) use ($scopeName, $scopeParameters) {
                /** @var Builder<EloquentModel> */
                return $model->$scopeName(...$scopeParameters);
            })
            ->pluck($this->titleColumn, (new $modelClass)->getKeyName());

        if ($value instanceof EloquentModel && ! $value instanceof $modelClass) {
            throw new InvalidArgumentException(
                sprintf('Value should be an instance of the [%s] model, instance of [%s] given.', $modelClass, get_class($value))
            );
        }

        $this->value = $value instanceof EloquentModel ? $value->getKey() : $value; // @phpstan-ignore assign.propertyType
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.model');
    }
}
