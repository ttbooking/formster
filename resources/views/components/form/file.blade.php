@use(function TTBooking\Formster\Support\prop_val)

@aware(['object', 'action', 'editable'])
@props(['property'])

@php($file = prop_val($property, $object))

@if ($file)
    @if ($action)
        <a {{ $attributes }}
           href="{{ $action.'/'.$property->variableName.($object ? '' : '/default') }}"
           @if ($file->contentDisposition === 'inline')
           target="_blank"
           @endif
           title="{{ basename($file) }}"
        >
            @if (config('formster.file.show_uploaded_name'))
                {{ basename($file) }}
            @elseif ($file->contentDisposition === 'inline')
                {{ __('formster::form.open') }}
            @else
                {{ __('formster::form.download') }}
            @endif
        </a>
    @else
        <span {{ $attributes }} title="{{ basename($file) }}">
            @if (config('formster.file.show_uploaded_name'))
                {{ basename($file) }}
            @else
                {{ __('formster::form.uploaded') }}
            @endif
        </span>
    @endif
@endif

@if ($object && $editable)
    <input {{ $attributes }}
        type="file"
        name="{{ $property->variableName }}"
        @if ($property->type->atomicParameters()->has(0))
        accept="{{ $property->type->atomicParameters()->get(0)->asConstExpr() }}"
        @endif
        {!! $property->type->name === 'list' ? 'multiple' : '' !!}
        @readonly(! $property->writable)
    />
@endif
