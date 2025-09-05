@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])

@if (! $object || ! $editable)
    @php($timezone = prop_val($property, $object))
    <span {{ $attributes }}>{{ $timezone->getName() }} ({{ now($timezone)->getOffsetString() }})</span>
@else
    <select {{ $attributes }} name="{{ $property->variableName }}" @disabled(! $property->writable)>
        @foreach ($timezones as $region => $timezone)
            @if (is_string($timezone))
                <option value="{{ $timezone }}" @selected($timezone === $object->{$property->variableName}->getName())>
                    {{ $timezone }} ({{ now($timezone)->getOffsetString() }})
                </option>
            @else
                <optgroup label="{{ $region }}">
                    @foreach ($timezone as $location)
                        <option value="{{ "$region/$location" }}" @selected("$region/$location" === $object->{$property->variableName}->getName())>
                            {{ $location }} ({{ now("$region/$location")->getOffsetString() }})
                        </option>
                    @endforeach
                </optgroup>
            @endif
        @endforeach
    </select>
@endif
