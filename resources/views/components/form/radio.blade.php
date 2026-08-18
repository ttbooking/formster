@use(function Illuminate\Support\enum_value)
@use(function TTBooking\Formster\Support\enum_desc)
@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable'])
@props(['property', 'value' => null])

@if (! $object || ! $editable)
    <span {{ $attributes }}>{{ enum_desc(prop_val($property, $object)) }}</span>
@else
    <fieldset {{ $attributes->except('name') }} @disabled(! $property->writable)>
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
