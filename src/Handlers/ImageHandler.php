<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use TTBooking\Formster\Types\Image;

class ImageHandler extends FileHandler
{
    protected const TYPE = Image::class;

    public function component(): string
    {
        return 'formster::form.image';
    }

    public function validationRules(): array
    {
        return $this->mergeValidationRules('required|image:allow_svg');
    }
}
