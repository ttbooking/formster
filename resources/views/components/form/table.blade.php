<table {{ $attributes }}>
    @if (isset($title) && $title->isNotEmpty() || $summary || $description)
        <caption>
            @if (! isset($title) || $title->isEmpty())
                @if ($summary)<h4>{!! Str::inlineMarkdown($summary) !!}</h4>@endif
                @if ($description)<h5>{!! Str::markdown($description) !!}</h5>@endif
            @else
                {{ $title }}
            @endif
        </caption>
    @endif
    <thead>
        <th>{{ __('formster::form.description') }}</th>
        <th>{{ __('formster::form.value') }}</th>
        @if ($showDefaults)
            <th>{{ __('formster::form.default') }}</th>
        @endif
    </thead>
    <tbody>
        @foreach ($aura->properties as $property)
            @can (array_unique([$aura->viewPolicy, $property->viewPolicy]), [$object, $property->variableName])
                <x-formster::form.row :$property />
            @endcan
        @endforeach
    </tbody>
</table>
