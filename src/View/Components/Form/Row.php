<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use TTBooking\Formster\Contracts\Comparable;
use TTBooking\Formster\Entities\AuraProperty;

use function TTBooking\Formster\Support\prop_desc;

class Row extends Component
{
    public object $object;

    public string $alias;

    public string $id;

    public string $description;

    public bool $changed;

    /**
     * Create a new component instance.
     */
    public function __construct(public AuraProperty $property)
    {
        $this->object = $this->factory()->getConsumableComponentData('object'); // @phpstan-ignore assign.propertyType
        $this->alias = $this->factory()->getConsumableComponentData('alias'); // @phpstan-ignore assign.propertyType

        $this->id = $this->alias.'_'.Str::snake($property->variableName);
        $this->description = prop_desc($this->object, $property->variableName, $property->description);

        $this->changed = $this->object->{$property->variableName} instanceof Comparable
            ? ! $this->object->{$property->variableName}->sameAs($property->defaultValue)
            : $this->object->{$property->variableName} != $property->defaultValue;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.row');
    }
}
