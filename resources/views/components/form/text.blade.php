@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ prop_val($property, $object) }}</span>
@else
    <input
        {{ $attributes->merge([
            'name' => $property->variableName,
            'value' => old($attributes->get('name'), $object->{$property->variableName}),
        ]) }}
        type="text"
        @readonly(! $property->writable)
    />
@endif
