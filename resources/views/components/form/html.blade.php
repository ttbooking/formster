@use(function TTBooking\Formster\Support\prop_val)

@aware(['object'])
@props(['property'])

@php
    /** @var TTBooking\Formster\Entities\FinalAuraProperty $property */
@endphp

{!! prop_val($property, $object)->toHtml() !!}
