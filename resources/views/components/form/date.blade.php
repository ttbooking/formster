@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@php
    /** @var TTBooking\Formster\Entities\FinalAuraProperty $property */
    /** @var Illuminate\Support\Carbon $datetime */
@endphp

@if (! $object || ! $editable)
    @php($datetime = prop_val($property, $object))
    <time {{ $attributes }} datetime="{{ $datetime->toDateString() }}">{{ $datetime->isoFormat('L') }}</time>
@else
    <input
        {{ $attributes->merge([
            'name' => $property->variableName,
            'value' => old($attributes->get('name', $property->variableName), $object->{$property->variableName}?->toDateString()),
        ]) }}
        type="date"
        @readonly(! $property->writable)
    />
@endif
