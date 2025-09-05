<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Facades\PropertyParser;
use TTBooking\Formster\Support\AliasResolver;

use function TTBooking\Formster\Support\prop_desc;

class Table extends Component
{
    public Aura $aura;

    public string $alias;

    public string $summary;

    public string $description;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public object $object,
        public ?string $action = null,
        public bool $showDefaults = true,
        public bool $editable = false,
    ) {
        $this->aura = $this->factory()->getConsumableComponentData('aura') // @phpstan-ignore assign.propertyType
            ?? PropertyParser::parse($object);
        $this->alias = AliasResolver::resolveAlias($object);

        $this->summary = prop_desc($object, '_summary', $this->aura->summary);
        $this->description = prop_desc($object, '_description', $this->aura->description);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.table');
    }
}
