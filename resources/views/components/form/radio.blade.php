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
    <fieldset {{ $attributes->except('name') }} @disabled(! $property->writable)>
        @if ($property->type->nullable)
            <label>
                <input
                    {{ $attributes->merge(['name' => $property->variableName])->only('name') }}
                    type="radio"
                    value=""
                    @checked(is_null($value ?? old($attributes->get('name', $property->variableName))))
                />
                <i>{{ __('formster::form.null') }}</i>
            </label>
        @endif
        @foreach ($property->type->name::cases() as $case)
            <label>
                <input
                    {{ $attributes->merge(['name' => $property->variableName])->only('name') }}
                    type="radio"
                    value="{{ enum_value($case) }}"
                    @checked(enum_value($case) == ($value ?? old($attributes->get('name', $property->variableName), enum_value($object->{$property->variableName}))))
                />
                {{ enum_desc($case) }}
            </label>
        @endforeach
    </fieldset>
@endif
