# Laravel Formster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![Tests](https://img.shields.io/github/actions/workflow/status/ttbooking/formster/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/ttbooking/formster/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![Total Downloads](https://img.shields.io/packagist/dt/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![License](https://img.shields.io/packagist/l/ttbooking/formster.svg?style=flat-square)](LICENSE.md)

**English** · [Русский](README.ru.md)

**Formster** is a Laravel library that **automatically generates HTML forms and read-only tables from any PHP object or Eloquent model**, based on property types. You don't have to describe every form field by hand: Formster reads the object's structure from PHPDoc annotations (`@property`), native PHP types, or PHP attributes, picks a suitable input widget for each property, and takes care of processing the submitted data.

```blade
{{-- The whole form — with every field, label, and a "Save" button — is generated in a single line --}}
<x-formster::form :object="$order" action="{{ route('orders.update', $order) }}" />
```

```php
// Handling the form submission is a one-liner too
Route::put('/orders/{order}', function (Request $request, Order $order) {
    ActionHandler::update($request, $order)->save();

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
- [Validation](#validation)
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
- ✅ **Automatic validation.** Every handler contributes default rules for its field; per-property rules are declared in the metadata and merged with the defaults via the `'...'` notation, and error messages are rendered next to the fields.
- 🖼️ **File and image pseudotypes** with `Storage` uploads, automatic previews (Intervention Image), and orphaned file cleanup.
- 🔒 **Laravel Gate integration.** Viewing and editing of every property is governed by policies (`viewPolicy` / `updatePolicy`), with a lenient mode by default and a switchable enforcing mode.
- 🌍 **Localization** of field labels, descriptions, and enum cases (English and Russian out of the box).
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

# the handler generator stub (to customize make:formster-handler)
php artisan vendor:publish --tag=formster-stub
```

---

## Quick start

### 1. Describe the model

PHPDoc `@property` annotations are enough as a minimum. No `$fillable`, casts, or manual form-field declarations are required — the annotated type determines the widget.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TTBooking\Formster\Entities\Aura;

/**
 * @property string $text Some text
 * @property int $number Some number
 * @property bool $flag Some flag
 */
#[Aura]
class Frankenstein extends Model
{
    protected $table = 'frankenstein';
}
```

> The `#[Aura]` attribute enables the lenient access-policy mode for the model: until you write policies, every property is visible and editable. Without the attribute (and without policies) the regular `Gate` denies access and the form comes out empty. See [Access control](#access-control-policies) for details.

### 2. Render a form or a table

```blade
{{-- Editable form (PUT method, "Save" button) --}}
<x-formster::form :object="$model" action="{{ route('update', $model) }}" />

{{-- Read-only table --}}
<x-formster::form.table :object="$model" />
```

### 3. Handle the submission

```php
use App\Models\Frankenstein;
use Illuminate\Http\Request;
use TTBooking\Formster\Facades\ActionHandler;

Route::get('/formster/{model}',      fn (Frankenstein $model) => view('table', compact('model')))->name('view');
Route::get('/formster/{model}/edit', fn (Frankenstein $model) => view('form', compact('model')))->name('edit');

Route::put('/formster/{model}', function (Request $request, Frankenstein $model) {
    ActionHandler::update($request, $model)->save();

    return back();
})->name('update');
```

`ActionHandler::update()` first validates the request (the rules are gathered from each property's handler and metadata, and error messages address fields by their localized descriptions), then walks over the model's writable properties (read-only ones are skipped), applies the appropriate handler to each field, respects access policies, and returns the modified object — all that's left is to call `->save()`.

---

## How it works

The full "model → form → submission" cycle consists of three stages.

```
                ┌─────────────────────┐
   object  ───► │   PropertyParser    │ ──► Aura { properties: AuraProperty[] }
                │ (aura,phpstan,refl.)│
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

1. **Parsing.** `PropertyParser::parse($object)` inspects the object and returns an aggregate **`Aura`** — a class description with a list of **`AuraProperty`** entries (name, type, readability/writability, default value, validation rules, access policies). Before use, `finalize()` filters the property list against the `include`/`exclude` lists of `#[Aura]` and turns the aggregate into the immutable **`FinalAura`** / **`FinalAuraProperty`** counterparts — this is what handlers and widgets receive.
2. **Handler selection.** For each property, `HandlerFactory::for($property)` picks the first `PropertyHandler` whose static `satisfies()` method matches the property type.
3. **Rendering and processing.** When rendering the form the handler's `component()` names the Blade widget. On submission the request is first validated against the rules from `validationRules()`, then `handle()` casts the value from the `Request` and writes it into the object.

### Key entities

| Entity                                    | Purpose                                                                                                                                                                                                                                                                     |
| ----------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`Aura`**                                | The class "aura": short (`summary`) and full (`description`) descriptions, a collection of properties (indexed by name), default policies (`viewPolicy`, `updatePolicy`), and the `include`/`exclude` property filters. Also serves as the class-level `#[Aura]` attribute. |
| **`AuraProperty`**                        | A single property description: `readable`, `writable`, `type`, `variableName`, `description`, `hasDefaultValue`/`defaultValue`, `validationRules`, `viewPolicy`, `updatePolicy`. Also serves as the `#[AuraProperty]` property attribute.                                   |
| **`FinalAura`** / **`FinalAuraProperty`** | The finalized (immutable, fully populated) counterparts produced by `Aura::finalize()` once all parsers have been merged. Handlers and Blade components work with these.                                                                                                    |
| **`AuraType`**                            | The type system: `AuraNamedType` (named/generic type), `AuraUnionType` (`A\|B`), `AuraIntersectionType` (`A&B`). Provides `contains()` and a `nullable` flag.                                                                                                               |

---

## Describing model properties

Formster supports **three ways** to declare properties, and they can be combined (see [Parsers](#property-parsers)).

### Way 1. PHPDoc annotations (recommended)

```php
/**
 * @property string $name              User name
 * @property int $age
 * @property string|null $bio          May be null
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
        type: new AuraNamedType('string'),
        description: 'Nickname',
        validationRules: ['...', 'min:3'],
    )]
    public string $nickname;
}
```

Every `#[AuraProperty]` parameter is optional — the attribute only complements or overrides what the other parsers have found. Properties can also be described at the class level via the `properties` parameter of `#[Aura]` (keyed by property name):

```php
#[Aura(properties: [
    'text' => new AuraProperty(validationRules: ['...', 'min:3']),
])]
class Frankenstein extends Model {}
```

The `#[Aura]` attribute can also decide which properties make it into the form: `include` is a whitelist (omit it and everything passes), `exclude` is a blacklist:

```php
#[Aura(exclude: ['password', 'remember_token'])]
class User extends Model {}
```

The filter is applied at the finalization stage, so it also strips properties discovered by the other parsers — PHPDoc or reflection. When auras are merged (aggregation, inheritance), both lists are combined; `exclude` always beats `include`.

Attributes are picked up from parent classes as well: the `aura` parser walks the whole inheritance chain and merges the metadata into a single aura — shared descriptions and filters can live on a base class. On conflicts the descendant wins: metadata declared closer to the class itself overrides the ancestors'.

---

## Property parsers

Parsers are responsible for extracting metadata. The active parsers and their order are set by the `formster.property_parser` option (default `aura,phpstan,reflection`).

| Driver               | Data source                                                                                                       |
| -------------------- | ----------------------------------------------------------------------------------------------------------------- |
| `aura`               | The `#[Aura]` / `#[AuraProperty]` PHP attributes — from the class itself and all of its ancestors                 |
| `reflection`         | Native typed `public` properties                                                                                  |
| `phpdoc`             | The class PHPDoc block via `phpdocumentor/reflection-docblock`                                                    |
| `phpstan`            | PHPDoc via `phpstan/phpdoc-parser` — supports generics, const expressions, and **recursive nested-class parsing** |
| `aggregate`          | Composite: combines several parsers                                                                               |
| (internal) `caching` | A decorator that caches the result of any parser                                                                  |

### Aggregation

If several drivers are listed comma-separated in `property_parser`, the `aggregate` driver is used automatically. It runs the object through each parser in turn and **merges** the results via `Aura::merge()`.

> **Order matters:** parsers listed later have priority. Same-named properties are merged field by field (`AuraProperty::merge()`): each piece of metadata (type, description, policy, …) is taken from the later parser when set there, and kept from the earlier one otherwise. Validation rules are merged using the `'...'` notation (see [Validation](#validation)).

### Caching

Parsing results are cached automatically (the `CachingParser` decorator). The store and TTL are configured via `formster.property_cache`. The cache key is `formster:properties:{driver}:{class}`.

---

## Property handlers

A handler (`PropertyHandler`) ties a property type to a widget, validation rules, and to the write logic. The contract:

```php
interface PropertyHandler
{
    public static function satisfies(FinalAuraProperty $property): bool; // does the type match?
    public function component(): string;                                 // Blade widget
    public function validationRules(): string|array;                     // validation rules for the field
    public function handle(object $object, Request $request): void;      // write the value
}
```

`HandlerFactory::for($property)` iterates over the handlers from the `formster.property_handlers` config and returns the first one whose `satisfies()` returned `true`. If none match, `FallbackHandler` is used.

> Handlers receive the finalized `FinalAuraProperty`. The rules returned by `validationRules()` are applied automatically when the submission is processed — see [Validation](#validation).

---

## Supported types and widgets

| Property type         | Handler               | Widget (Blade)               | HTML field                          | Default validation rules       |
| --------------------- | --------------------- | ---------------------------- | ----------------------------------- | ------------------------------ |
| `bool`                | `BooleanHandler`      | `form.checkbox`              | `<input type="checkbox">`           | `sometimes\|in:on`             |
| `int`                 | `IntegerHandler`      | `form.number`                | `<input type="number">`             | `required\|integer`            |
| `float`               | `FloatHandler`        | `form.decimal`               | `<input type="number" step="0.01">` | `required\|numeric`            |
| `string`              | `StringHandler`       | `form.text`                  | `<input type="text">`               | `required\|string`             |
| `BackedEnum`          | `EnumHandler`         | `form.radio` / `form.select` | radio buttons or a dropdown         | `required` + `Rule::enum(...)` |
| `DateTimeInterface`   | `DateTimeHandler`     | `form.datetime`              | `<input type="datetime-local">`     | `required\|date`               |
| `DateTimeZone`        | `DateTimeZoneHandler` | `form.timezone`              | `<select>` with time zones          | `required\|timezone`           |
| `Color`               | `ColorHandler`        | `form.color`                 | `<input type="color">`              | `required\|hex_color`          |
| `File` / `list<File>` | `FileHandler`         | `form.file`                  | `<input type="file">`               | `required\|file`               |
| `Image`               | `ImageHandler`        | `form.image`                 | `<input type="file">` + preview     | `required\|image:allow_svg`    |
| *anything else*       | `FallbackHandler`     | `form.disclaimer`            | an "unsupported type" message       | —                              |

**Enum.** `EnumHandler` renders radio buttons (`radio`) when the number of cases does not exceed the `buttonLimit` threshold (default **2**), and a dropdown (`select`) otherwise.

Enum case descriptions are localized (see [Localization](#localization)); if no translation is found, the case's PHPDoc comment or its "humanized" name is used.

---

## Validation

Before writing anything, `ActionHandler::update()` validates the request with the standard `$request->validate()`. The rules are collected automatically:

1. Every handler contributes default rules for its type via `validationRules()` (see the table above).
2. Per-property rules are declared with the `validationRules` parameter of the `#[AuraProperty]` attribute — as a string (`'required|min:3'`), an array, or a closure returning a rule list (closures in attributes require PHP ≥ 8.5).
3. Rules declared on a property **replace** the handler defaults. To **extend** the defaults instead, include the `'...'` element — it is substituted with the handler's rules (the lists are flattened and de-duplicated):

```php
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraProperty;

#[Aura(properties: [
    // resulting rules: required|string|min:3
    'text' => new AuraProperty(validationRules: ['...', 'min:3']),
])]
class Frankenstein extends Model {}
```

On failure the usual Laravel mechanics kick in (redirect back with `$errors`). The widgets render the message below the field in a `formster-validation-failed` block, and error messages address the field by its localized description (see [Localization](#localization)).

---

## Pseudotypes and casts

Beyond scalar types, Formster provides four **pseudotypes** in the `TTBooking\Formster\Types` namespace. They implement `Castable`, so declaring them in the model's `casts()` is enough — Eloquent picks the right cast, and Formster derives the matching widget.

```php
use TTBooking\Formster\Types\{Color, DateTimeZone, File, Image};

/**
 * @property Color $brand_color
 * @property DateTimeZone $timezone
 * @property File $manual
 * @property Image $photo
 */
class Product extends Model
{
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

The constructor validates the format (`/^#[0-9a-fA-F]{6}$/`) and throws `InvalidArgumentException` on error.

### DateTimeZone

Extends the native `\DateTimeZone`, rendered as a `<select>` grouped by region. The group can be controlled via the pseudotype's parameters in the annotation:

```php
/**
 * All zones, grouped by region (default):
 * @property DateTimeZone $tz
 *
 * US-only zones (two-letter ISO country code):
 * @property DateTimeZone<"US"> $tz_us
 */
```

### File

File upload via `Storage`. In edit mode it's an `<input type="file">`, in view mode it's a download/open link.

Pseudotype parameters are set via a generic annotation: `File<TAccept, TDisposition, TDisk>`.

```php
/**
 * PDF documents, "attachment" disposition (download), "documents" disk:
 * @property File<"application/pdf", "attachment", "documents"> $contract
 *
 * Multiple files (the field gets the multiple attribute):
 * @property list<File> $attachments
 */
```

- `TAccept` — a MIME-type filter for the `accept` attribute (default `*/*`);
- `TDisposition` — `attachment` (download) or `inline` (open in the browser);
- `TDisk` — the filesystem disk (default from config).

> A `list<File>` field renders a multi-file input and the form gets the proper `enctype`, but server-side processing of multiple uploaded files is not implemented yet — `FileHandler` currently skips array uploads.

**Stored file names.** By default `hashName()` is used. The logic can be overridden globally, e.g. in a service provider's `boot()`:

```php
use Illuminate\Http\UploadedFile;
use TTBooking\Formster\Entities\FinalAuraProperty;
use TTBooking\Formster\Types\File;

File::generateStorableNamesUsing(function (object $object, FinalAuraProperty $property, UploadedFile $uploadedFile, ?string $disk) {
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
| -------------------------- | ------------------------------------------------------------------ | -------------------------------------------------- |
| `<x-formster::form>`       | A full `<form>` (POST + `@method('PUT')`, "Save" button)           | `:object`, `action`, `:show-defaults`              |
| `<x-formster::form.table>` | A table of properties (view or edit)                               | `:object`, `action`, `:editable`, `:show-defaults` |
| `<x-formster::form.row>`   | A table row for a single property                                  | `:property`                                        |
| `<x-formster::form.input>` | An input widget for a property (picks the component via a handler) | `:property`, `:object`                             |

**Examples:**

```blade
{{-- A ready-made form with a "Save" button --}}
<x-formster::form :object="$model" action="{{ route('users.update', $model) }}" />

{{-- Table only, without the defaults column --}}
<x-formster::form.table :object="$model" :show-defaults="false" />

{{-- A custom button instead of the default one (via a slot) --}}
<x-formster::form :object="$model" action="{{ route('users.update', $model) }}">
    <x-slot:buttons>
        <button type="submit">Update profile</button>
    </x-slot:buttons>
</x-formster::form>
```

> If any of the properties is a file or an image — including fields declared as `list<File>` — the form automatically gets `enctype="multipart/form-data"`.

### Widget components (anonymous)

Each widget can also be called directly: `form.text`, `form.number`, `form.decimal`, `form.checkbox`, `form.radio`, `form.select`, `form.datetime`, `form.color`, `form.timezone`, `form.file`, `form.image`, `form.disclaimer`.

```blade
<x-formster::form.text :property="$property" />
```

Widgets use `@aware` to inherit context from the parent table (`object`, `editable`; the file widgets also inherit `action`) and show either an editable field or a read-only view. When validation fails, the error message is rendered below the field in a `<div class="formster-validation-failed">` block.

To change the markup, publish the templates (`vendor:publish --tag=formster-views`) and edit the files in `resources/views/vendor/formster`.

---

## Access control (policies)

The visibility and editability of every property are checked through **Laravel Gate**. Both `Aura` and `AuraProperty` carry policies:

- `viewPolicy` (default `view`) — whether the property may be **shown**;
- `updatePolicy` (default `update`) — whether the property may be **edited**.

Where and how the checks run:

- **table row visibility** — the class-level and property-level `viewPolicy` combined, arguments `[$object, $propertyName]`;
- **row editability** and **submission processing** — the class-level and property-level `updatePolicy` combined, same arguments;
- **the "Save" button** — the class-level `updatePolicy` only, with just the object as the argument (no property name).

The policy method receives the model and the property name:

```php
class OrderPolicy
{
    // $property — the property name, e.g. 'email'
    public function view(User $user, Order $order, ?string $property = null): bool
    {
        return $property !== 'secret_field';
    }

    public function update(User $user, Order $order, ?string $property = null): bool
    {
        return $user->isAdmin();
    }
}
```

### Lenient mode — the default

By default Formster runs in a lenient mode: access is granted automatically when

- **no** policy is defined for the object, **or**
- the policy **has no method** for the ability being checked.

This lets you use Formster without writing any policies, adding restrictions gradually — only where they are needed.

It is implemented via the `TTBooking\Formster\Support\LenientPolicy` `Gate::before()` callback, registered in the service provider. The callback only steps in for objects marked with the `#[Aura]` attribute, and only when the policy/method is missing: in that case it returns `true` (full access). In all other cases it returns `null` and hands control to the standard `Gate::check()`. Objects without `#[Aura]` are left untouched.

> **Important:** the lenient mode only applies to classes marked with the `#[Aura]` attribute — on the class itself or on any of its ancestors. A model described by PHPDoc annotations alone (without the attribute) is **not** covered by the lenient mode: if no policy is defined for it, the regular `Gate::check()` denies access and its properties are not shown. So either mark your models with `#[Aura]`, or write policies.

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

| Key                              | EN                         |
| -------------------------------- | -------------------------- |
| `description`                    | Parameter                  |
| `value`                          | Value                      |
| `default`                        | Default                    |
| `na`                             | N/A                        |
| `null`                           | NULL                       |
| `on` / `off`                     | ✔️ / ❌                    |
| `open` / `download` / `uploaded` | open / download / uploaded |
| `save`                           | Save                       |

### Property and enum case descriptions

A field description is looked up by translation keys (application strings take priority over the package ones):

```
formster.{model|object}.{alias}.{property_name_in_snake_case}
```

For enum cases:

```
formster.enum.{alias}.{enum_case_in_snake_case}
```

For example, for the `App\Models\User` model and the `firstName` property:

```php
// lang/{locale}/formster.php
return [
    'model' => [
        'user' => [
            'first_name' => 'First name',
            '_summary'     => 'User profile',       // table heading
            '_description' => 'Basic data',         // table description
        ],
    ],
];
```

If no translation is found, the PHPDoc annotation text is used; if that is absent too, the description is generated from the property name (`Str::headline`).

---

## Aliases

An `alias` is the key under which a model/enum appears in localization strings. By default it is derived from the class name (dropping the `App\Models\` / `App\Enums\` namespace and converting the rest to `snake_case`). You can pin your own alias with the `#[Alias]` attribute:

```php
use TTBooking\Formster\Attributes\Alias;

#[Alias('person')]
class Customer extends Model {}
```

---

## Configuration

After publishing (`vendor:publish --tag=formster-config`) the `config/formster.php` file is available:

```php
return [

    // The property parser(s). Several — comma-separated (enables aggregation).
    'property_parser' => env('FORMSTER_PROPERTY_PARSER', 'aura,phpstan,reflection'),

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

    // Policy enforcement:
    // false — lenient mode (grant access when policy/method is missing),
    // true — the regular Laravel Gate behavior.
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

| Variable                                | Purpose                      | Default                   |
| --------------------------------------- | ---------------------------- | ------------------------- |
| `FORMSTER_PROPERTY_PARSER`              | Property parser(s)           | `aura,phpstan,reflection` |
| `FORMSTER_ENFORCE_POLICIES`             | Policy enforcement mode      | `false`                   |
| `FORMSTER_PROPERTY_CACHE_STORE`         | Cache store                  | standard                  |
| `FORMSTER_PROPERTY_CACHE_TTL`           | Cache TTL (sec)              | forever                   |
| `FORMSTER_DISK`                         | Disk for uploads             | default disk              |
| `FORMSTER_STATIC_DISK`                  | Disk for static files        | `FORMSTER_DISK`           |
| `FORMSTER_CONTENT_DISPOSITION`          | File disposition             | `attachment`              |
| `FORMSTER_SHOW_FILENAME`                | Show the file name           | `true`                    |
| `FORMSTER_PREVIEW_WIDTH` / `_HEIGHT`    | Preview size                 | `100` / `100`             |
| `FORMSTER_PREVIEW_SCALE_DOWN_THRESHOLD` | Preview scale-down threshold | `10240`                   |

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
use TTBooking\Formster\Entities\FinalAuraProperty;

class MoneyHandler implements PropertyHandler
{
    public function __construct(public FinalAuraProperty $property) {}

    public static function satisfies(FinalAuraProperty $property): bool
    {
        return $property->type->contains(Money::class);
    }

    public function component(): string
    {
        return 'formster::form.money';
    }

    public function validationRules(): string|array
    {
        return $this->property->mergeValidationRules();
    }

    public function handle(object $object, Request $request): void
    {
        $object->{$this->property->variableName} = new Money($request->{$this->property->variableName});
    }
}
```

`mergeValidationRules($defaults)` merges the rules declared on the property with the handler's default rules using the `'...'` notation (see [Validation](#validation)).

Register the handler in `config/formster.php` (in the `property_handlers` array, before `FallbackHandler`) and create the `form.money` Blade widget.

The generator stub can be published (`vendor:publish --tag=formster-stub`) and customized — the command picks up `stubs/formster-handler.stub` from the application root.

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

| Facade            | Class                   | Purpose                                                                               |
| ----------------- | ----------------------- | ------------------------------------------------------------------------------------- |
| `PropertyParser`  | `PropertyParserManager` | `parse($objectOrClass): Aura` — parse an object/class into metadata                   |
| `PropertyHandler` | `HandlerFactory`        | `for(FinalAuraProperty $property): PropertyHandler` — pick a handler                  |
| `ActionHandler`   | `ActionHandler`         | `update(Request $request, object $object): object` — apply request data to the object |

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

CI runs a matrix of PHP 8.2–8.5 × Laravel 12.17 / 13.0 (prefer-lowest and prefer-stable); the PHP 8.2 × Laravel 13 combination is excluded.

---

## License

Formster is released under the **MIT** license. See the [LICENSE.md](LICENSE.md) file for details.
