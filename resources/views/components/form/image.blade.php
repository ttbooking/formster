@use(TTBooking\Formster\Types\Image)
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
            @if ($file->exists() && $preview = $file->preview())
                <img
                    src="{{ $preview }}"
                    alt="{{ basename($file) }}"
                    @style([
                        'margin: 5px 0',
                        'max-width: '.Image::previewWidth().'px',
                        'max-height: '.Image::previewHeight().'px',
                        'object-fit: scale-down',
                    ])
                />
            @elseif (config('formster.file.show_uploaded_name'))
                {{ basename($file) }}
            @elseif ($file->contentDisposition === 'inline')
                {{ __('formster::form.open') }}
            @else
                {{ __('formster::form.download') }}
            @endif
        </a>
    @else
        <span {{ $attributes }} title="{{ basename($file) }}">
            @if ($file->exists() && $preview = $file->preview())
                <img
                    src="{{ $preview }}"
                    alt="{{ basename($file) }}"
                    @style([
                        'margin: 5px 0',
                        'max-width: '.Image::previewWidth().'px',
                        'max-height: '.Image::previewHeight().'px',
                        'object-fit: scale-down',
                    ])
                />
            @elseif (config('formster.file.show_uploaded_name'))
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
        @else
        accept="image/*"
        @endif
        {!! $property->type->name === 'list' ? 'multiple' : '' !!}
        @readonly(! $property->writable)
    />
@endif
