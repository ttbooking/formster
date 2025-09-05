<table {{ $attributes }}>
    <caption>
        @if (! isset($title) || $title->isEmpty())
            <h4>{!! Str::inlineMarkdown($summary) !!}</h4>
            <h5>{!! Str::markdown($description) !!}</h5>
        @else
            {{ $title }}
        @endif
    </caption>
    <thead>
        <th>{{ __('formster::form.description') }}</th>
        <th>{{ __('formster::form.value') }}</th>
        @if ($showDefaults)
            <th>{{ __('formster::form.default') }}</th>
        @endif
    </thead>
    <tbody>
        @foreach ($aura->properties as $property)
            <x-formster::form.row :$property />
        @endforeach
    </tbody>
</table>
