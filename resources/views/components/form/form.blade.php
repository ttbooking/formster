<form {{ $attributes->only(['id', 'action', 'enctype'])->merge($mergeAttrs) }} method="POST">
    @csrf
    @method('PUT')
    <x-formster::form.table {{ $attributes->except(['id', 'enctype']) }} :$object :$showDefaults :editable="true">
        @isset($title)
            <x-slot:title>{{ $title }}</x-slot:title>
        @endisset
    </x-formster::form.table>
    @if (! isset($buttons) || $buttons->isEmpty())
        @can($aura->updatePolicy, $object)
            <button type="submit">{{ __('formster::form.save') }}</button>
        @endcan
    @else
        {{ $buttons }}
    @endif
</form>
