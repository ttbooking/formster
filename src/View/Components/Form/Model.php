<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Entities\FinalAuraProperty;

class Model extends Component
{
    use AssertsPropertyTypes;

    public string $titleColumn;

    /** @var Collection<array-key, mixed> */
    public Collection $options;

    /**
     * Create a new component instance.
     */
    public function __construct(public FinalAuraProperty $property)
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $this->namedType()->name;
        $this->titleColumn = $this->namedType()->atomicParameters()->get(0)?->asConstExpr() ?? 'name'; // @phpstan-ignore assign.propertyType
        $scopeName = $this->namedType()->atomicParameters()->get(1)?->asConstExpr() ?? null;
        $scopeParameters = Arr::wrap($this->namedType()->atomicParameters()->get(2)?->asConstExpr() ?? []);

        $this->options = $modelClass::query()
            ->when($scopeName, static function ($model) use ($scopeName, $scopeParameters) {
                /** @var Builder<\Illuminate\Database\Eloquent\Model> */
                return $model->$scopeName(...$scopeParameters);
            })
            ->pluck($this->titleColumn, (new $modelClass)->getKeyName());
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.model');
    }
}
