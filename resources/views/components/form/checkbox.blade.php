@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property', 'value' => null])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ __(prop_val($property, $object) ? 'formster::form.on' : 'formster::form.off') }}</span>
@else
    <span {{ $attributes->except(['id', 'name']) }}>
        <input {{ $attributes->merge(['name' => $property->variableName])->only('name') }} type="hidden" value="0" />
        <input
            {{ $attributes->merge(['name' => $property->variableName])->only(['id', 'name']) }}
            type="checkbox"
            value="1"
            @checked($value ?? old($attributes->get('name', $property->variableName), $object->{$property->variableName}))
            @disabled(! $property->writable)
        />
    </span>
@endif
