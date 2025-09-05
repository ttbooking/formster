<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Facades\PropertyParser;
use TTBooking\Formster\Types\File;

class Form extends Component
{
    public Aura $aura;

    /** @var array<string, mixed> */
    public array $mergeAttrs = [];

    /**
     * Create a new component instance.
     */
    public function __construct(public object $object, public bool $showDefaults = true)
    {
        $this->aura = PropertyParser::parse($object);

        $containsFileProperty = collect($this->aura->properties)
            ->contains(static fn (AuraProperty $property) => $property->type->contains(File::class));

        $this->mergeAttrs = $containsFileProperty ? ['enctype' => 'multipart/form-data'] : [];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.form');
    }
}
