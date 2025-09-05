@use(function Illuminate\Support\enum_value)
@use(function TTBooking\Formster\Support\enum_desc)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ enum_desc(prop_val($property, $object)) }}</span>
@else
    <select {{ $attributes }} name="{{ $property->variableName }}" @disabled(! $property->writable)>
        @foreach ($property->type->name::cases() as $case)
            <option value="{{ enum_value($case) }}" @selected(enum_value($case) === enum_value($object->{$property->variableName}))>
                {{ enum_desc($case) }}
            </option>
        @endforeach
    </select>
@endif
