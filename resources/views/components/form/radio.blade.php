@use(function Illuminate\Support\enum_value)
@use(function TTBooking\Formster\Support\enum_desc)
@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable', 'handler'])
@props(['property', 'value' => null])

@php
    /** @var TTBooking\Formster\Handlers\EnumHandler $handler */
    /** @var TTBooking\Formster\Entities\FinalAuraProperty $property */
@endphp

@if (! $object || ! $editable)
    @if ($handler->multiple)
        @if (count($selectedCases = prop_val($property, $object)))
            <span {{ $attributes->except('id') }}>
                @foreach ($selectedCases as $case)
                    {{ enum_desc($case) }}<br />
                @endforeach
            </span>
        @else
            <i {{ $attributes->except('id') }}>{{ __('formster::form.empty') }}</i>
        @endif
    @else
        <span {{ $attributes->except('id') }}>{{ enum_desc(prop_val($property, $object)) }}</span>
    @endif
@else
    <fieldset {{ $attributes->except('name') }} @disabled(! $property->writable)>
        @if (! $handler->multiple && $property->type->nullable)
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
        @foreach ($handler->enumCases as $case)
            <label>
                @if ($handler->multiple)
                    <input
                        {{ $attributes->merge(['name' => $property->variableName.'[]'])->only('name') }}
                        type="checkbox"
                        value="{{ enum_value($case) }}"
                        @checked($object->{$property->variableName}->contains($case))
                    />
                @else
                    <input
                        {{ $attributes->merge(['name' => $property->variableName])->only('name') }}
                        type="radio"
                        value="{{ enum_value($case) }}"
                        @checked(enum_value($case) == ($value ?? old($attributes->get('name', $property->variableName), enum_value($object->{$property->variableName}))))
                    />
                @endif
                {{ enum_desc($case) }}
            </label>
        @endforeach
    </fieldset>
@endif
