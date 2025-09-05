<?php

declare(strict_types=1);

namespace TTBooking\Formster\Types;

use Intervention\Image\EncodedImage;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;
use TTBooking\Formster\Casts\AsImage;

/**
 * @template TAccept of string = "image/*"
 * @template TDisposition of string = "inline"
 * @template TDisk of string|null = null
 *
 * @extends File<TAccept, TDisposition, TDisk>
 */
class Image extends File
{
    /**
     * @param  TDisk  $disk
     * @param  TDisposition  $contentDisposition
     */
    public function __construct(
        string $name,
        ?string $disk = null,
        string $contentDisposition = 'inline',
        ?string $mediaType = null,
    ) {
        parent::__construct($name, $disk, $contentDisposition, $mediaType);
    }

    public function asDataUri(): ?string
    {
        if (is_null($data = $this->get())) {
            return null;
        }

        return (new EncodedImage($data, $this->mediaType()))->toDataUri();
    }

    public function preview(): ?string
    {
        if (is_null($data = $this->get())) {
            return null;
        }

        if ($this->mediaType() === 'image/svg+xml' || $this->size() <= static::previewScaleDownThreshold()) {
            $preview = new EncodedImage($data, $this->mediaType());
        } else {
            try {
                $preview = InterventionImage::read($data)
                    ->scaleDown(static::previewWidth(), static::previewHeight())
                    ->encode();
            } catch (DecoderException) {
                return null;
            }
        }

        return $preview->toDataUri();
    }

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     * @return class-string<AsImage>
     */
    public static function castUsing(array $arguments): string
    {
        return AsImage::class;
    }

    public static function contentDisposition(): string
    {
        return 'inline';
    }

    public static function previewWidth(): int
    {
        /** @var int */
        return config('formster.preview.width', 100);
    }

    public static function previewHeight(): int
    {
        /** @var int */
        return config('formster.preview.height', 100);
    }

    public static function previewScaleDownThreshold(): int
    {
        /** @var int */
        return config('formster.preview.scale_down_threshold', 10_240);
    }
}
