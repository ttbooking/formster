@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ __(prop_val($property, $object) ? 'formster::form.on' : 'formster::form.off') }}</span>
@else
    <input type="hidden" name="{{ $property->variableName }}" value="0" />
    <input {{ $attributes }}
        type="checkbox"
        name="{{ $property->variableName }}"
        value="1"
        @checked(old($property->variableName, $object->{$property->variableName}))
        @disabled(! $property->writable)
    />
@endif
