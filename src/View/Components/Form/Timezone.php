<?php

declare(strict_types=1);

namespace TTBooking\Formster\View\Components\Form;

use Closure;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use InvalidArgumentException;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Entities\AuraProperty;

class Timezone extends Component
{
    use AssertsPropertyTypes;

    /** @var Collection<int, string>|Collection<string, Collection<int, string>> */
    public Collection $timezones;

    /**
     * Create a new component instance.
     */
    public function __construct(public AuraProperty $property)
    {
        $timezoneGroup = $this->namedType()->atomicParameters()->get(0)?->asConstExpr() ?? DateTimeZone::ALL;
        $groupByRegion = $this->namedType()->atomicParameters()->get(1)?->asConstExpr() ?? $timezoneGroup === DateTimeZone::ALL;

        [$timezoneGroup, $countryCode] = match (true) {
            is_int($timezoneGroup) => [$timezoneGroup, null],
            is_string($timezoneGroup) => [DateTimeZone::PER_COUNTRY, strtoupper($timezoneGroup)],
            default => throw new InvalidArgumentException(
                'Timezone group must be either a DateTimeZone group constant or a two-letter ISO 3166-1 country code.'
            ),
        };

        $this->timezones = collect(DateTimeZone::listIdentifiers($timezoneGroup, $countryCode));

        if ($groupByRegion) {
            $this->timezones = $this->timezones // @phpstan-ignore assign.propertyType
                ->mapToGroups(static fn (string $tz) => [($_ = explode('/', $tz, 2))[0] => $_[1] ?? $_[0]])
                ->map(static fn (Collection $tzs, string $region) => $tzs->all() === [$region] ? $region : $tzs);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('formster::components.form.timezone');
    }
}
