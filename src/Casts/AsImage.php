<?php

declare(strict_types=1);

namespace TTBooking\Formster\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use TTBooking\Formster\Types\Image;

/**
 * @implements CastsAttributes<Image, Image>
 */
class AsImage extends AsFile implements CastsAttributes
{
    protected const TYPE = Image::class;
}
