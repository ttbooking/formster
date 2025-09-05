@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ __(prop_val($property, $object) ? 'formster::form.on' : 'formster::form.off') }}</span>
@else
    <input {{ $attributes }} type="checkbox" name="{{ $property->variableName }}" @checked($object->{$property->variableName}) @disabled(! $property->writable) />
@endif
