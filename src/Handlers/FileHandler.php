<?php

declare(strict_types=1);

namespace TTBooking\Formster\Handlers;

use Illuminate\Http\Request;
use TTBooking\Formster\Concerns\AssertsPropertyTypes;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraNamedType;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Types\File;

class FileHandler implements PropertyHandler
{
    use AssertsPropertyTypes;

    /** @var class-string<File> */
    protected const TYPE = File::class;

    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return $property->type instanceof AuraNamedType && (
            is_a($property->type->name, static::TYPE, true) ||
            $property->type->name === 'list' &&
            is_a($property->type->atomicParameters()->get(0)->name ?? '', static::TYPE, true)
        );
    }

    public function component(): string
    {
        return 'formster::form.file';
    }

    public function handle(object $object, Request $request): void
    {
        if (! ($file = $request->file($this->property->variableName)) || is_array($file)) {
            return;
        }

        $this->deleteFileIfNotStaticOrDefault($object);

        $disk = $this->getDisk();
        $name = File::generateStorableName($object, $this->property, $file, $disk);

        if (! $name = $file->storeAs($name, compact('disk'))) {
            return;
        }

        $object->{$this->property->variableName} = $this->newInstance($name, $disk);
    }

    public function validate(Request $request): bool
    {
        return true;
    }

    protected function deleteFileIfNotStaticOrDefault(object $object): bool
    {
        $maybeFile = $object->{$this->property->variableName};

        return $maybeFile instanceof File
            && ! str_starts_with($maybeFile->name, '/')
            && ! $maybeFile->sameAs($this->property->defaultValue)
            && $maybeFile->delete();
    }

    protected function newInstance(string $name, ?string $disk = null): File
    {
        return new (static::TYPE)($name, $disk, $this->getContentDisposition());
    }

    protected function getDisk(): ?string
    {
        /** @var string|null */
        return $this->namedType()->atomicParameters()->get(2)?->asConstExpr() ?? static::TYPE::disk();
    }

    protected function getContentDisposition(): string
    {
        /** @var string */
        return $this->namedType()->atomicParameters()->get(1)?->asConstExpr() ?? static::TYPE::contentDisposition();
    }
}
