<?php

namespace Illuminate\Support\Facades;

if (class_exists('Illuminate\Support\Facades\Image')) {
    return;
}

class Image
{
    public static function fromStorage(string $path, ?string $disk = null): \Illuminate\Image\Image
    {
        return new \Illuminate\Image\Image('');
    }
}
