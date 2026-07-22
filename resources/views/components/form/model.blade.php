@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ prop_val($property, $object)->$titleColumn }}</span>
@else
    <select {{ $attributes }} name="{{ $property->variableName }}" @disabled(! $property->writable)>
        @foreach ($options as $key => $title)
            <option value="{{ $key }}" @selected($key === old($property->variableName, $object->{$property->variableName}?->getKey()))>
                {{ $title }}
            </option>
        @endforeach
    </select>
@endif
