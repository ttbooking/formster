<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use TTBooking\Formster\Contracts\PropertyHandler as PropertyHandlerContract;
use TTBooking\Formster\Entities\FinalAuraProperty;
use TTBooking\Formster\Facades\PropertyHandler;

class Input extends Component
{
    public PropertyHandlerContract $handler;

    public string $component;

    /**
     * Create a new component instance.
     */
    public function __construct(public FinalAuraProperty $property, public ?object $object = null)
    {
        $this->handler = PropertyHandler::for($property);
        $this->component = $this->handler->component();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.input');
    }
}
