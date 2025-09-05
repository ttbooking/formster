<?php

declare(strict_types=1);

namespace TTBooking\Formster\Types;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JsonSerializable;
use Stringable;
use TTBooking\Formster\Casts\AsFile;
use TTBooking\Formster\Contracts\Comparable;
use TTBooking\Formster\Entities\AuraProperty;

/**
 * @template TAccept of string = "\*\/\*"
 * @template TDisposition of string = "attachment"
 * @template TDisk of string|null = null
 */
class File implements Castable, Comparable, JsonSerializable, Stringable
{
    /** @var null|Closure(object, AuraProperty, UploadedFile, string|null): string */
    protected static ?Closure $storableNamesGenerator = null;

    /**
     * @param  TDisk  $disk
     * @param  TDisposition  $contentDisposition
     */
    public function __construct(
        public string $name,
        public ?string $disk = null,
        public string $contentDisposition = 'attachment',
        protected ?string $mediaType = null,
    ) {}

    public function __toString(): string
    {
        return $this->name;
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }

    public function sameAs(mixed $that): bool
    {
        return $that instanceof $this
            && $that->disk === $this->disk
            && $that->name === $this->name;
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->name);
    }

    public function size(): int
    {
        return Storage::disk($this->disk)->size($this->name);
    }

    public function get(): ?string
    {
        return Storage::disk($this->disk)->get($this->name);
    }

    /**
     * @internal
     */
    public function delete(): bool
    {
        return Storage::disk($this->disk)->delete($this->name);
    }

    public function mediaType(): string
    {
        return $this->mediaType ??= MimeType::from($this->name);
    }

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     * @return class-string<AsFile>
     */
    public static function castUsing(array $arguments): string
    {
        return AsFile::class;
    }

    public static function disk(): ?string
    {
        /** @var string|null */
        return config('formster.file.disk');
    }

    public static function staticDisk(): ?string
    {
        /** @var string|null */
        return config('formster.file.static_disk');
    }

    public static function contentDisposition(): string
    {
        /** @var string */
        return config('formster.file.content_disposition', 'attachment');
    }

    public static function generateStorableName(
        object $object,
        AuraProperty $property,
        UploadedFile $file,
        ?string $disk = null,
    ): string {
        return static::$storableNamesGenerator
            ? (static::$storableNamesGenerator)($object, $property, $file, $disk)
            : $file->hashName();
    }

    /**
     * @param  Closure(object $object, AuraProperty $property, UploadedFile $file, string|null $disk): string  $callback
     * @return class-string<static>
     */
    public static function generateStorableNamesUsing(Closure $callback): string
    {
        static::$storableNamesGenerator = $callback;

        return static::class;
    }

    /**
     * @return class-string<static>
     */
    public static function generateStorableNamesNormally(): string
    {
        static::$storableNamesGenerator = null;

        return static::class;
    }
}
