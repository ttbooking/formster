@use(function TTBooking\Formster\Support\number_format)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ number_format(prop_val($property, $object)) }}</span>
@else
    <input {{ $attributes }} type="number" name="{{ $property->variableName }}" value="{{ $object->{$property->variableName} }}" @readonly(! $property->writable) />
@endif
