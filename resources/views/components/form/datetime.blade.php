@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    @php($datetime = prop_val($property, $object))
    <time {{ $attributes }} datetime="{{ $datetime->toDateTimeLocalString('minute') }}">{{ $datetime->isoFormat('L LT') }}</time>
@else
    <input {{ $attributes }}
        type="datetime-local"
        name="{{ $property->variableName }}"
        value="{{ $object->{$property->variableName}->toDateTimeLocalString('minute') }}"
        @readonly(! $property->writable)
    />
@endif
