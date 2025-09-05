@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }} title="{{ prop_val($property, $object) }}"
        @style([
            'padding: 0 18px',
            'border: 4px solid #efefef',
            'outline: 1px solid #767676',
            'border-radius: 1px',
            'line-height: 27px',
            'background-color: '.prop_val($property, $object),
        ])
    ></span>
@else
    <input {{ $attributes }} type="color" name="{{ $property->variableName }}" value="{{ $object->{$property->variableName} }}" @readonly(! $property->writable) />
@endif
