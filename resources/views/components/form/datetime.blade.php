@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    @php($datetime = prop_val($property, $object))
    <time {{ $attributes }} datetime="{{ $datetime->toDateTimeLocalString('minute') }}">{{ $datetime->isoFormat('L LT') }}</time>
@else
    <input
        {{ $attributes->merge([
            'name' => $property->variableName,
            'value' => old($attributes->get('name', $property->variableName), $object->{$property->variableName}?->toDateTimeLocalString('minute')),
        ]) }}
        type="datetime-local"
        @readonly(! $property->writable)
    />
@endif
