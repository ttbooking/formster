<?php

namespace Illuminate\Image;

if (class_exists('Illuminate\Image\ImageException')) {
    return;
}

use RuntimeException;

class ImageException extends RuntimeException {}
