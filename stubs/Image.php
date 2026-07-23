<?php

namespace Illuminate\Image;

if (class_exists('Illuminate\Image\Image')) {
    return;
}

use Illuminate\Support\Traits\Conditionable;

class Image
{
    use Conditionable;

    public function scale(?int $width = null, ?int $height = null): static
    {
        return $this;
    }

    public function toDataUri(): string
    {
        return '';
    }
}
