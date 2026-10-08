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
    <time {{ $attributes }} datetime="{{ $datetime->toDateTimeLocalString('minute') }}">{{ $datetime->isoFormat('L LT') }}</time>
@else
    <input
        {{ $attributes->merge([
            'name' => $property->variableName,
            'value' => old($attributes->get('name', $property->variableName), $object->{$property->variableName}?->toDateTimeLocalString('minute')),
        ]) }}
        type="datetime-local"
        @if (isset($property->meta['presets']) && count($property->meta['presets']))
        list="{{ $attributes->get('id').'_presets' }}"
        @endif
        @readonly(! $property->writable)
    />
    @if (isset($property->meta['presets']) && count($property->meta['presets']))
        <datalist id="{{ $attributes->get('id').'_presets' }}">
            @foreach ($property->meta['presets'] as $preset)
                <option value="{{ $preset }}"></option>
            @endforeach
        </datalist>
    @endif
@endif
