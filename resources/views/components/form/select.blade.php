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
    <select {{ $attributes->merge(['name' => $property->variableName.($handler->multiple ? '[]' : '')]) }} @if ($handler->multiple) multiple @endif @disabled(! $property->writable)>
        @if (! $handler->multiple && $property->type->nullable)
            <option value="" @selected(is_null($value ?? old($attributes->get('name', $property->variableName))))>-- {{ __('formster::form.null') }} --</option>
        @endif
        @foreach ($handler->enumCases as $case)
            @if ($handler->multiple)
                <option value="{{ enum_value($case) }}" @selected($object->{$property->variableName}->contains($case))>
                    {{ enum_desc($case) }}
                </option>
            @else
                <option value="{{ enum_value($case) }}" @selected(enum_value($case) == ($value ?? old($attributes->get('name', $property->variableName), enum_value($object->{$property->variableName}))))>
                    {{ enum_desc($case) }}
                </option>
            @endif
        @endforeach
    </select>
@endif
