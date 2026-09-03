@use(function TTBooking\Formster\Support\old)
@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'editable', 'typeParameters'])
@props(['property'])

@if (! $object || ! $editable)
    @if ($property->type->contains('list<string>') || ($typeParameters[0] ?? false))
        <span {{ $attributes }}><pre @style('white-space: pre-wrap')>{{ implode("\n", (array) prop_val($property, $object)) }}</pre></span>
    @else
        <span {{ $attributes }} @style('word-wrap: break-word')>{{ prop_val($property, $object) }}</span>
    @endif
@elseif ($multiline = $property->type->contains('list<string>') || ($typeParameters[0] ?? false))
    <textarea
        {{ $attributes->except('value')->merge(['name' => $property->variableName]) }}
        @style([
            'field-sizing: content' => $multiline === true,
            'box-sizing: border-box' => $multiline === true,
            'width: 100%' => $multiline === true,
            'max-width: 100%' => $multiline === true,
            'resize: none' => $multiline === true,
            'resize: vertical' => is_int($multiline),
            'min-height: calc(1lh + 2px)' => is_int($multiline),
        ])
        @if (is_int($multiline))
        rows="{{ $multiline }}"
        @endif
    >{{
        $attributes->get('value') ?? old($attributes->get('name', $property->variableName), implode("\n", (array) $object->{$property->variableName}))
    }}</textarea>
@else
    <input
        {{ $attributes->merge([
            'name' => $property->variableName,
            'value' => old($attributes->get('name', $property->variableName), $object->{$property->variableName}),
        ]) }}
        type="text"
        @readonly(! $property->writable)
    />
@endif
