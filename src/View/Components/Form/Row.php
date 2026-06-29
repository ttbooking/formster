<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use TTBooking\Formster\Contracts\Comparable;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraProperty;

use function TTBooking\Formster\Support\prop_desc;

class Row extends Component
{
    public Aura $aura;

    public object $object;

    public string $alias;

    public bool $editable;

    public string $id;

    public string $description;

    public bool $changed;

    /**
     * Create a new component instance.
     */
    public function __construct(public AuraProperty $property)
    {
        $this->aura = $this->factory()->getConsumableComponentData('aura'); // @phpstan-ignore assign.propertyType
        $this->object = $this->factory()->getConsumableComponentData('object'); // @phpstan-ignore assign.propertyType
        $this->alias = $this->factory()->getConsumableComponentData('alias'); // @phpstan-ignore assign.propertyType
        $this->editable = $this->factory()->getConsumableComponentData('editable') && Gate::allows(
            array_unique([$this->aura->updatePolicy, $property->updatePolicy]),
            [$this->object, $property->variableName]
        );

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
