@use(function TTBooking\Formster\Support\number_format)
@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property'])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ number_format(prop_val($property, $object)) }}</span>
@else
    <input
        {{ $attributes->merge([
            'name' => $property->variableName,
            'value' => old($attributes->get('name', $property->variableName), $object->{$property->variableName}),
        ]) }}
        type="number"
        step="0.01"
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
