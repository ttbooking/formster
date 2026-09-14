@use(function Illuminate\Support\enum_value)
@use(function TTBooking\Formster\Support\enum_desc)
@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property', 'value' => null])

@php
    /** @var TTBooking\Formster\Entities\FinalAuraProperty $property */
@endphp

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ enum_desc(prop_val($property, $object)) }}</span>
@else
    <select {{ $attributes->merge(['name' => $property->variableName]) }} @disabled(! $property->writable)>
        @if ($property->type->nullable)
            <option value="" @selected(is_null($value ?? old($attributes->get('name', $property->variableName))))>{{ '-- '.__('formster::form.null').' --' }}</option>
        @endif
        @foreach ($property->type->name::cases() as $case)
            <option value="{{ enum_value($case) }}" @selected(enum_value($case) == ($value ?? old($attributes->get('name', $property->variableName), enum_value($object->{$property->variableName}))))>
                {{ enum_desc($case) }}
            </option>
        @endforeach
    </select>
@endif
