<?php

declare(strict_types=1);

namespace TTBooking\Formster\Types;

use Illuminate\Image\Image as ImageObject;
use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Image as IlluminateImage;
use Intervention\Image\Decoders\BinaryImageDecoder;
use Intervention\Image\EncodedImage;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\Interfaces\AnimationFactoryInterface;
use Intervention\Image\Interfaces\ImageInterface;
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

    public function asDataUri(bool $preview = false): ?string
    {
        return match (true) {
            class_exists(IlluminateImage::class) => $this->illuminateDataUri($preview),
            class_exists(InterventionImage::class) => $this->interventionDataUri($preview),
            default => null,
        };
    }

    public function preview(): ?string
    {
        return $this->asDataUri(true);
    }

    protected function illuminateDataUri(bool $preview = false): ?string
    {
        try {
            return IlluminateImage::fromStorage($this->name, $this->disk)
                ->when(
                    $preview && $this->requiresScaleDownForPreview(),
                    static fn (ImageObject $image) => $image->scale(static::previewWidth(), static::previewHeight())
                )
                ->toDataUri();
        } catch (ImageException) {
            return null;
        }
    }

    protected function interventionDataUri(bool $preview = false): ?string
    {
        if (is_null($data = $this->get())) {
            return null;
        }

        if ($preview && $this->requiresScaleDownForPreview()) {
            try {
                /** @var ImageInterface $image */
                $image = interface_exists(AnimationFactoryInterface::class)
                    ? InterventionImage::decode($data, BinaryImageDecoder::class)
                    : InterventionImage::read($data, BinaryImageDecoder::class);

                $encoded = $image
                    ->scaleDown(static::previewWidth(), static::previewHeight())
                    ->encode();
            } catch (DecoderException) {
                return null;
            }
        } else {
            $encoded = new EncodedImage($data, $this->mediaType());
        }

        return (string) $encoded->toDataUri();
    }

    protected function requiresScaleDownForPreview(): bool
    {
        return $this->mediaType() !== 'image/svg+xml' && $this->size() > static::previewScaleDownThreshold();
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

    /**
     * @return positive-int
     */
    public static function previewWidth(): int
    {
        /** @var positive-int */
        return config('formster.preview.width', 100);
    }

    /**
     * @return positive-int
     */
    public static function previewHeight(): int
    {
        /** @var positive-int */
        return config('formster.preview.height', 100);
    }

    /**
     * @return positive-int
     */
    public static function previewScaleDownThreshold(): int
    {
        /** @var positive-int */
        return config('formster.preview.scale_down_threshold', 10_240);
    }
}
