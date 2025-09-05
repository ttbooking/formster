<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Facades\PropertyHandler;

class Input extends Component
{
    public string $component;

    /**
     * Create a new component instance.
     */
    public function __construct(public AuraProperty $property, public ?object $object = null)
    {
        $this->component = PropertyHandler::for($property)->component();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.input');
    }
}
