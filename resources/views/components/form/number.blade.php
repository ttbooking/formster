@use(function TTBooking\Formster\Support\number_format)
@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ number_format(prop_val($property, $object)) }}</span>
@else
    <input
        {{ $attributes->merge([
            'name' => $property->variableName,
            'value' => old($attributes->get('name', $property->variableName), $object->{$property->variableName}),
        ]) }}
        type="number"
        @readonly(! $property->writable)
    />
@endif
