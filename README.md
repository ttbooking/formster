# Laravel Formster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![Tests](https://img.shields.io/github/actions/workflow/status/ttbooking/formster/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/ttbooking/formster/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![Total Downloads](https://img.shields.io/packagist/dt/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![License](https://img.shields.io/packagist/l/ttbooking/formster.svg?style=flat-square)](LICENSE.md)

[Русский](README.ru.md) · **English**

**Formster** is a Laravel library that **automatically generates HTML forms and read-only tables from any PHP object or Eloquent model**, based on property types. You don't have to describe every form field by hand: Formster reads the object's structure from PHPDoc annotations (`@property`), native PHP types, or PHP attributes, picks a suitable input widget for each property, and takes care of processing the submitted data.

```blade
{{-- The whole form — with every field, label, and a "Save" button — is generated in a single line --}}
<x-formster::form :object="$user" action="{{ route('users.update', $user) }}" />
```

```php
// Handling the form submission is a one-liner too
Route::put('/users/{user}', function (Request $request, User $user) {
    ActionHandler::update($request, $user)->save();

    return back();
});
```

---

## Table of contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [How it works](#how-it-works)
- [Describing model properties](#describing-model-properties)
- [Property parsers](#property-parsers)
- [Property handlers](#property-handlers)
- [Supported types and widgets](#supported-types-and-widgets)
- [Pseudotypes and casts](#pseudotypes-and-casts)
  - [Color](#color)
  - [DateTimeZone](#datetimezone)
  - [File](#file)
  - [Image](#image)
- [Blade components](#blade-components)
- [Access control (policies)](#access-control-policies)
- [Localization](#localization)
- [Aliases](#aliases)
- [Configuration](#configuration)
- [Writing your own handler](#writing-your-own-handler)
- [Cleaning up orphaned files](#cleaning-up-orphaned-files)
- [Facades and public API](#facades-and-public-api)
- [Testing and code quality](#testing-and-code-quality)
- [License](#license)

---

## Features

- 🚀 **Templateless forms.** Declare a model — Formster builds an editable form or a read-only table for it.
- 🧠 **Multiple metadata sources.** Properties are extracted from PHPDoc (`@property`), native PHP types (reflection), and the `#[Aura]` / `#[AuraProperty]` PHP attributes. Sources can be combined.
- 🧩 **Rich type system.** Support for union (`A|B`), intersection (`A&B`), nullable, generics (`Collection<int, User>`, `list<File>`, `class-string<User>`), and recursive parsing of nested classes.
- 🎛️ **Ready-made widgets** for strings, integers, floats, booleans, enums, dates, time zones, colors, files, and images.
- 🖼️ **File and image pseudotypes** with `Storage` uploads, automatic previews (Intervention Image), and old-file cleanup.
- 🔒 **Laravel Gate integration.** Viewing and editing of every property is governed by policies (`viewPolicy` / `updatePolicy`), with a lenient mode by default and a switchable enforcing mode.
- 🌍 **Localization** of field labels, descriptions, and enum values (English and Russian out of the box).
- ⚡ **Caching** of parsing results.
- 🛠️ **Extensibility** — custom type handlers are scaffolded by an Artisan command.

---

## Requirements

- PHP **8.2+** (tested on 8.2–8.5)
- Laravel **^12.17 || ^13.0**
- `intervention/image-laravel` `^1.5 || ^4.0` (for image previews)

---

## Installation

```bash
composer require ttbooking/formster
```

The package uses auto-discovery, so the service provider and facades are registered automatically.

If needed, publish the configuration and/or the view templates:

```bash
# configuration
php artisan vendor:publish --tag=formster-config

# Blade widget templates (to customize markup)
php artisan vendor:publish --tag=formster-views
```

---

## Quick start

### 1. Describe the model

PHPDoc `@property` annotations are enough as a minimum. No `$fillable`, casts, or manual form-field declarations are required — the annotated type determines the widget.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $text
 * @property int $integer
 * @property bool $flag
 */
class Frankenstein extends Model
{
    protected $table = 'frankenstein';
}
```

### 2. Render a form or a table

```blade
{{-- Editable form (PUT method, "Save" button) --}}
<x-formster::form :object="$model" action="{{ route('update', $model) }}" />

{{-- Read-only table --}}
<x-formster::form.table :object="$model" />
```

### 3. Handle the submission

```php
use Illuminate\Http\Request;
use TTBooking\Formster\Facades\ActionHandler;
use App\Models\Frankenstein;

Route::get('/formster/{model}',      fn (Frankenstein $model) => view('table', compact('model')))->name('view');
Route::get('/formster/{model}/edit', fn (Frankenstein $model) => view('form', compact('model')))->name('edit');

Route::put('/formster/{model}', function (Request $request, Frankenstein $model) {
    ActionHandler::update($request, $model)->save();

    return redirect()->back();
})->name('update');
```

`ActionHandler::update()` walks over all model properties, applies the appropriate handler to each field from the request, respects access policies, and returns the modified object — all that's left is to call `->save()`.

---

## How it works

The full "model → form → submission" cycle consists of three stages.

```
                ┌─────────────────────┐
   object  ───► │   PropertyParser    │ ──► Aura { properties: AuraProperty[] }
                │  (phpstan,reflection)│
                └─────────────────────┘
                           │
                           ▼  for each property
                ┌─────────────────────┐
                │   HandlerFactory    │ ──► PropertyHandler (by property type)
                └─────────────────────┘
                           │
            ┌──────────────┴───────────────┐
            ▼                              ▼
   component() → Blade widget      handle($obj, $request) → write value
   (rendering the form)            (processing the submission)
```

1. **Parsing.** `PropertyParser::parse($object)` inspects the object and returns an aggregate **`Aura`** — a class description with a list of **`AuraProperty`** entries (name, type, readability/writability, default value, access policies).
2. **Handler selection.** For each property, `HandlerFactory::for($property)` picks the first `PropertyHandler` whose static `satisfies()` method matches the property type.
3. **Rendering and processing.** When rendering the form the handler's `component()` names the Blade widget. On submission `handle()` casts the value from the `Request` and writes it into the object.

### Key entities

| Entity             | Purpose                                                                                                                                                                                                                     |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`Aura`**         | The class "aura": short (`summary`) and full (`description`) descriptions, a collection of properties (indexed by name), and default policies (`viewPolicy`, `updatePolicy`). Also serves as the class-level `#[Aura]` attribute. |
| **`AuraProperty`** | A single property description: `readable`, `writable`, `type`, `variableName`, `description`, `hasDefaultValue`/`defaultValue`, `viewPolicy`, `updatePolicy`. Also serves as the `#[AuraProperty]` property attribute.     |
| **`AuraType`**     | The type system: `AuraNamedType` (named/generic type), `AuraUnionType` (`A\|B`), `AuraIntersectionType` (`A&B`). Provides `contains()` and a `nullable` flag.                                                             |

---

## Describing model properties

Formster supports **three ways** to declare properties, and they can be combined (see [Parsers](#property-parsers)).

### Way 1. PHPDoc annotations (recommended)

```php
/**
 * @property string $name              User name
 * @property int $age
 * @property ?string $bio              May be null
 * @property \App\Enums\Status $status
 * @property-read int $id              Read-only
 * @property-write string $password    Write-only
 */
class User extends Model {}
```

- `@property` — the property is both readable and writable;
- `@property-read` — read-only (shown in the form but not editable);
- `@property-write` — write-only.

The text after the type and name becomes the field description.

### Way 2. Native PHP types (reflection)

```php
class Dto
{
    public string $name;
    public int $age = 18;            // the default value is picked up automatically
    public readonly string $id;      // readonly → read-only
    public ?Color $color = null;
}
```

### Way 3. PHP attributes

For full control over the metadata you can attach the `#[Aura]` and `#[AuraProperty]` attributes directly:

```php
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Entities\AuraNamedType;

#[Aura(summary: 'Profile', description: 'User data')]
class Profile
{
    #[AuraProperty(
        readable: true,
        writable: true,
        type: new AuraNamedType('string'),
        variableName: 'nickname',
        description: 'Nickname',
    )]
    public string $nickname;
}
```

---

## Property parsers

Parsers are responsible for extracting metadata. The active parsers and their order are set by the `formster.property_parser` option (default `phpstan,reflection`).

| Driver               | Data source                                                                                                     |
| -------------------- | -------------------------------------------------------------------------------------------------------------- |
| `aura`               | The `#[Aura]` / `#[AuraProperty]` PHP attributes                                                                |
| `reflection`         | Native typed `public` properties                                                                               |
| `phpdoc`             | The class PHPDoc block via `phpdocumentor/reflection-docblock`                                                 |
| `phpstan`            | PHPDoc via `phpstan/phpdoc-parser` — supports generics, const expressions, and **recursive nested-class parsing** |
| `aggregate`          | Composite: combines several parsers                                                                            |
| (internal) `caching` | A decorator that caches the result of any parser                                                              |

### Aggregation

If several drivers are listed comma-separated in `property_parser`, the `aggregate` driver is used automatically. It runs the object through each parser in turn and **merges** the results via `Aura::merge()`.

> **Order matters:** parsers listed later have priority — their non-empty values override data from the earlier ones. For example, with `phpstan,reflection` the PHPDoc data is complemented and, on collision, overridden by information from native types.

### Caching

Parsing results are cached automatically (the `CachingParser` decorator). The store and TTL are configured via `formster.property_cache`. The cache key is `formster:properties:{driver}:{class}`.

---

## Property handlers

A handler (`PropertyHandler`) ties a property type to a widget and to the write logic. The contract:

```php
interface PropertyHandler
{
    public static function satisfies(AuraProperty $property): bool; // does the type match
    public function component(): string;                            // Blade widget
    public function handle(object $object, Request $request): void; // write the value
    public function validate(Request $request): bool;               // validation
}
```

`HandlerFactory::for($property)` iterates over the handlers from the `formster.property_handlers` config and returns the first one whose `satisfies()` returned `true`. If none match, `FallbackHandler` is used.

---

## Supported types and widgets

| Property type         | Handler               | Widget (Blade)               | HTML field                          |
| --------------------- | --------------------- | ---------------------------- | ----------------------------------- |
| `bool`                | `BooleanHandler`      | `form.checkbox`              | `<input type="checkbox">`           |
| `int`                 | `IntegerHandler`      | `form.number`                | `<input type="number">`             |
| `float`               | `FloatHandler`        | `form.decimal`               | `<input type="number" step="0.01">` |
| `string`              | `StringHandler`       | `form.text`                  | `<input type="text">`               |
| `BackedEnum`          | `EnumHandler`         | `form.radio` / `form.select` | radio buttons or a dropdown         |
| `DateTimeInterface`   | `DateTimeHandler`     | `form.datetime`              | `<input type="datetime-local">`     |
| `DateTimeZone`        | `DateTimeZoneHandler` | `form.timezone`              | `<select>` with time zones          |
| `Color`               | `ColorHandler`        | `form.color`                 | `<input type="color">`              |
| `File` / `list<File>` | `FileHandler`         | `form.file`                  | `<input type="file">`               |
| `Image`               | `ImageHandler`        | `form.image`                 | `<input type="file">` + preview     |
| *anything else*       | `FallbackHandler`     | `form.disclaimer`            | an "unsupported type" message       |

**Enum.** `EnumHandler` renders radio buttons (`radio`) when the number of options does not exceed the `buttonLimit` threshold (default **2**), and a dropdown (`select`) otherwise.

Enum option descriptions are localized (see [Localization](#localization)); if no translation is found, the case's PHPDoc comment or its "humanized" name is used.

---

## Pseudotypes and casts

Beyond scalar types, Formster provides four **pseudotypes** in the `TTBooking\Formster\Types` namespace. They implement `Castable`, so declaring them in the model's `casts()` is enough — Eloquent picks the right cast, and Formster derives the matching widget.

```php
use TTBooking\Formster\Types\{Color, DateTimeZone, File, Image};

class Product extends Model
{
    /**
     * @property ?Color $brand_color
     * @property ?DateTimeZone $timezone
     * @property ?File $manual
     * @property ?Image $photo
     */
    protected function casts(): array
    {
        return [
            'brand_color' => Color::class,
            'timezone'    => DateTimeZone::class,
            'manual'      => File::class,
            'photo'       => Image::class,
        ];
    }
}
```

### Color

A HEX color in `#RRGGBB` format. Rendered as `<input type="color">`, and as a colored swatch in view mode.

```php
$product->brand_color = new Color('#3366ff');
```

The constructor validates the format (`/^#[a-zA-Z0-9]{6}$/`) and throws `InvalidArgumentException` on error.

### DateTimeZone

Extends the native `\DateTimeZone`, rendered as a `<select>` grouped by region. The group can be controlled via the pseudotype's parameters in the annotation:

```php
/**
 * All zones, grouped by region (default):
 * @property ?DateTimeZone $tz
 *
 * Russia-only zones (two-letter ISO country code):
 * @property ?DateTimeZone<'RU'> $tz_ru
 */
```

### File

File upload via `Storage`. In edit mode it's an `<input type="file">`, in view mode it's a download/open link.

Pseudotype parameters are set via a generic annotation: `File<TAccept, TDisposition, TDisk>`.

```php
/**
 * PDF documents, "attachment" disposition (download), "documents" disk:
 * @property ?File<'application/pdf', 'attachment', 'documents'> $contract
 *
 * Multiple files (the field gets the multiple attribute):
 * @property list<File> $attachments
 */
```

- `TAccept` — a MIME-type filter for the `accept` attribute (default `*/*`);
- `TDisposition` — `attachment` (download) or `inline` (open in the browser);
- `TDisk` — the filesystem disk (default from config).

**Stored file names.** By default `hashName()` is used. The logic can be overridden globally, e.g. in a service provider's `boot()`:

```php
use TTBooking\Formster\Types\File;

File::generateStorableNamesUsing(function ($object, $property, $uploadedFile, $disk) {
    return 'uploads/'.$uploadedFile->getClientOriginalName();
});

// restore the default behavior:
File::generateStorableNamesNormally();
```

When a new file is uploaded, the old one is deleted automatically (unless it is "static" or equals the default value). Static files are those whose name starts with `/` — they live on a separate `static_disk` and are never deleted.

### Image

Inherits from `File`, but accepts `image/*` by default, opens `inline`, and **shows a preview**.

The preview is generated via **Intervention Image**: the image is scaled down to `formster.preview.width × height`. SVGs and files smaller than the `scale_down_threshold` are served as-is. Both major versions of Intervention Image are supported.

---

## Blade components

All components are available under the `formster::` namespace.

### Structural components

| Component                  | Purpose                                                            | Main parameters                                    |
| -------------------------- | ----------------------------------------------------------------- | -------------------------------------------------- |
| `<x-formster::form>`       | A full `<form>` (POST + `@method('PUT')`, "Save" button)          | `:object`, `action`, `:show-defaults`              |
| `<x-formster::form.table>` | A table of properties (view or edit)                              | `:object`, `action`, `:editable`, `:show-defaults` |
| `<x-formster::form.row>`   | A table row for a single property                                 | `:property`                                        |
| `<x-formster::form.input>` | An input widget for a property (picks the component via a handler) | `:property`, `:object`                             |

**Examples:**

```blade
{{-- A ready-made form with a "Save" button --}}
<x-formster::form :object="$model" action="{{ route('users.update', $model) }}" />

{{-- Table only, without the defaults column --}}
<x-formster::form.table :object="$model" :editable="false" :show-defaults="false" />

{{-- A custom button instead of the default one (via a slot) --}}
<x-formster::form :object="$model" action="{{ route('users.update', $model) }}">
    <x-slot:buttons>
        <button type="submit">Update profile</button>
    </x-slot:buttons>
</x-formster::form>
```

> If any of the properties is a file or an image, the form automatically gets `enctype="multipart/form-data"`.

### Widget components (anonymous)

Each widget can also be called directly: `form.text`, `form.number`, `form.decimal`, `form.checkbox`, `form.radio`, `form.select`, `form.datetime`, `form.color`, `form.timezone`, `form.file`, `form.image`, `form.disclaimer`.

```blade
<x-formster::form.text :property="$property" />
```

Widgets use `@aware` to inherit context (`object`, `editable`, `action`) from the parent table and show either an editable field or a read-only view.

To change the markup, publish the templates (`vendor:publish --tag=formster-views`) and edit the files in `resources/views/vendor/formster`.

---

## Access control (policies)

The visibility and editability of every property are checked through **Laravel Gate**. Both `Aura` and `AuraProperty` carry policies:

- `viewPolicy` (default `view`) — whether the property may be **shown**;
- `updatePolicy` (default `update`) — whether the property may be **edited**.

When rendering the table and when processing the submission, Formster calls the corresponding policy method on the model, passing the model and the property name:

```php
class UserPolicy
{
    // $property — the property name, e.g. 'email'
    public function view(?User $authUser, User $model, string $property): bool
    {
        return $property !== 'secret_field';
    }

    public function update(?User $authUser, User $model, string $property): bool
    {
        return $authUser?->isAdmin() ?? false;
    }
}
```

### Lenient mode — the default

By default Formster runs in a lenient mode: access is granted automatically when

- **no** policy is defined for the object, **or**
- the policy **has no method** for the ability being checked.

This lets you use Formster without writing any policies, adding restrictions gradually — only where they are needed.

It is implemented via the `TTBooking\Formster\Support\LenientPolicy` `Gate::before()` callback, registered in the service provider. The callback only steps in for objects marked with the `#[Aura]` attribute, and only when the policy/method is missing: in that case it returns `true` (full access). In all other cases it returns `null` and hands control to the standard `Gate::check()`. Objects without `#[Aura]` are left untouched.

### Enforcing mode

If you want the regular Laravel Gate behavior (missing policy/method → access **denied**), enable the enforcing mode:

```dotenv
FORMSTER_ENFORCE_POLICIES=true
```

or in `config/formster.php`:

```php
'enforce_policies' => true,
```

When `enforce_policies = true`, the `LenientPolicy` callback is not registered, and the outcome of every check is fully determined by your policies via `Gate::check()`.

---

## Localization

Interface labels and property descriptions are translatable. The package ships with English and Russian strings; they can be published and extended.

### Interface labels

The `lang/vendor/formster/{locale}/form.php` file:

| Key                              | EN                           |
| -------------------------------- | ---------------------------- |
| `description`                    | Parameter                    |
| `value`                          | Value                        |
| `default`                        | Default                      |
| `na`                             | N/A                          |
| `null`                           | NULL                         |
| `on` / `off`                     | ✔️ / ❌                      |
| `open` / `download` / `uploaded` | open / download / uploaded   |
| `save`                           | Save                         |

### Property and enum-value descriptions

A field description is looked up by translation keys (application strings take priority over the package ones):

```
formster.{model|object}.{alias}.{property_in_snake_case}
```

For enum values:

```
formster.enum.{alias}.{case_in_snake_case}
```

For example, for the `App\Models\User` model and the `firstName` property:

```php
// lang/{locale}/formster.php
return [
    'model' => [
        'user' => [
            'first_name' => 'First name',
            '_summary'     => 'User profile',       // table heading
            '_description' => 'Basic data',          // table description
        ],
    ],
];
```

If no translation is found, the description is taken from the PHPDoc annotation text, and as a last resort it is generated from the property name (`Str::headline`).

---

## Aliases

An `alias` is the key under which a model/enum appears in localization strings. By default it is derived from the class name (dropping the `App\Models\` / `App\Enums\` namespace and converting the rest to `snake_case`). You can pin your own alias with the `#[Alias]` attribute:

```php
use TTBooking\Formster\Attributes\Alias;

#[Alias('user')]
class Customer extends Model {}
```

---

## Configuration

After publishing (`vendor:publish --tag=formster-config`) the `config/formster.php` file is available:

```php
return [

    // The property parser(s). Several — comma-separated (enables aggregation).
    'property_parser' => env('FORMSTER_PROPERTY_PARSER', 'phpstan,reflection'),

    // Parsing-result cache.
    'property_cache' => [
        'store' => env('FORMSTER_PROPERTY_CACHE_STORE'),      // cache store (default — the standard one)
        'ttl'   => (int) env('FORMSTER_PROPERTY_CACHE_TTL') ?: null, // TTL (null — forever)
    ],

    // Active property handlers (order = satisfies() check priority).
    'property_handlers' => [
        TTBooking\Formster\Handlers\BooleanHandler::class,
        TTBooking\Formster\Handlers\IntegerHandler::class,
        TTBooking\Formster\Handlers\FloatHandler::class,
        TTBooking\Formster\Handlers\StringHandler::class,
        TTBooking\Formster\Handlers\EnumHandler::class,
        TTBooking\Formster\Handlers\DateTimeHandler::class,
        TTBooking\Formster\Handlers\DateTimeZoneHandler::class,
        TTBooking\Formster\Handlers\ColorHandler::class,
        TTBooking\Formster\Handlers\ImageHandler::class,
        TTBooking\Formster\Handlers\FileHandler::class,
    ],

    // Policy enforcement. false — lenient mode (access when policy/method is
    // missing), true — the regular Laravel Gate behavior.
    'enforce_policies' => (bool) env('FORMSTER_ENFORCE_POLICIES', false),

    // File pseudotype settings.
    'file' => [
        'disk'                => env('FORMSTER_DISK'),                         // disk for uploads
        'static_disk'         => env('FORMSTER_STATIC_DISK', env('FORMSTER_DISK')), // disk for static files
        'content_disposition' => env('FORMSTER_CONTENT_DISPOSITION', 'attachment'),
        'show_uploaded_name'  => (bool) env('FORMSTER_SHOW_FILENAME', true),   // show the file name in the link
    ],

    // Preview settings for the Image pseudotype.
    'preview' => [
        'width'                => (int) env('FORMSTER_PREVIEW_WIDTH', 100),
        'height'               => (int) env('FORMSTER_PREVIEW_HEIGHT', 100),
        'scale_down_threshold' => (int) env('FORMSTER_PREVIEW_SCALE_DOWN_THRESHOLD', 10_240), // bytes
    ],

];
```

### Environment variables

| Variable                                | Purpose                     | Default              |
| --------------------------------------- | --------------------------- | -------------------- |
| `FORMSTER_PROPERTY_PARSER`              | Property parser(s)          | `phpstan,reflection` |
| `FORMSTER_ENFORCE_POLICIES`             | Policy enforcement mode     | `false`              |
| `FORMSTER_PROPERTY_CACHE_STORE`         | Cache store                 | standard             |
| `FORMSTER_PROPERTY_CACHE_TTL`           | Cache TTL (sec)             | forever              |
| `FORMSTER_DISK`                         | Disk for uploads            | default disk         |
| `FORMSTER_STATIC_DISK`                  | Disk for static files       | `FORMSTER_DISK`      |
| `FORMSTER_CONTENT_DISPOSITION`          | File disposition            | `attachment`         |
| `FORMSTER_SHOW_FILENAME`                | Show the file name          | `true`               |
| `FORMSTER_PREVIEW_WIDTH` / `_HEIGHT`    | Preview size                | `100` / `100`        |
| `FORMSTER_PREVIEW_SCALE_DOWN_THRESHOLD` | Preview scale-down threshold | `10240`             |

---

## Writing your own handler

To add support for a new type, scaffold a handler with the command:

```bash
php artisan make:formster-handler MoneyHandler --type=Money
```

- `--type` (`-t`) — the handled type or class;
- `--force` (`-f`) — overwrite an existing class.

The command is interactive: with no arguments it asks for a name and lets you pick a type from the `app/Formster/Types` directory. The class is created in the `App\Formster\Handlers` namespace.

The generated handler:

```php
namespace App\Formster\Handlers;

use App\Formster\Types\Money;
use Illuminate\Http\Request;
use TTBooking\Formster\Contracts\PropertyHandler;
use TTBooking\Formster\Entities\AuraProperty;

class MoneyHandler implements PropertyHandler
{
    public function __construct(public AuraProperty $property) {}

    public static function satisfies(AuraProperty $property): bool
    {
        return $property->type->contains(Money::class);
    }

    public function component(): string
    {
        return 'formster::form.money';
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = new Money($request->{$this->property->variableName});
    }

    public function validate(Request $request): bool
    {
        return true;
    }
}
```

Register the handler in `config/formster.php` (in the `property_handlers` array, before `FileHandler`/`FallbackHandler`) and create the `form.money` Blade widget.

The generator stub can be published and customized by placing `stubs/handler.stub` in the application root.

---

## Cleaning up orphaned files

To have uploaded files deleted when a model is deleted, attach the `OrphanedFileCollector` observer:

```php
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use TTBooking\Formster\Observers\OrphanedFileCollector;

#[ObservedBy(OrphanedFileCollector::class)]
class Product extends Model {}
```

When the model is deleted, the observer removes all attached files (`File`/`Image`) except static ones (name starting with `/`). On a **soft** delete (SoftDeletes without a force delete) the files are kept.

---

## Facades and public API

| Facade            | Class                   | Purpose                                                                                 |
| ----------------- | ----------------------- | --------------------------------------------------------------------------------------- |
| `PropertyParser`  | `PropertyParserManager` | `parse($objectOrClass): Aura` — parse an object/class into metadata                     |
| `PropertyHandler` | `HandlerFactory`        | `for(AuraProperty $property): PropertyHandler` — pick a handler                         |
| `ActionHandler`   | `ActionHandler`         | `update(Request $request, object $object): object` — apply request data to the object   |

```php
use TTBooking\Formster\Facades\PropertyParser;

$aura = PropertyParser::parse(App\Models\User::class);

foreach ($aura->properties as $property) {
    echo $property->variableName.': '.$property->type.PHP_EOL;
}
```

---

## Testing and code quality

The package uses **Pest**, **PHPStan (larastan, level max)**, and **Laravel Pint**. Available composer scripts:

```bash
composer test      # run the tests (Pest)
composer analyse   # static analysis (PHPStan)
composer lint      # code-style check (Pint --test)
composer serve     # run the demo app (workbench)
```

CI runs a matrix of PHP 8.2–8.5 × Laravel 12.17 / 13.0 (prefer-lowest and prefer-stable).

---

## License

Formster is released under the **MIT** license. See the [LICENSE.md](LICENSE.md) file for details.
