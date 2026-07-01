# Laravel Formster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![Tests](https://img.shields.io/github/actions/workflow/status/ttbooking/formster/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/ttbooking/formster/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![Total Downloads](https://img.shields.io/packagist/dt/ttbooking/formster.svg?style=flat-square)](https://packagist.org/packages/ttbooking/formster)
[![License](https://img.shields.io/packagist/l/ttbooking/formster.svg?style=flat-square)](LICENSE.md)

**Русский** · [English](README.md)

**Formster** — это Laravel-библиотека, которая **автоматически генерирует HTML-формы и таблицы-просмотры из любого PHP-объекта или Eloquent-модели**, опираясь на типы свойств. Вам не нужно вручную описывать каждое поле формы: Formster читает структуру объекта из PHPDoc-аннотаций (`@property`), нативных типов PHP или PHP-атрибутов, подбирает подходящий виджет ввода для каждого свойства и берёт на себя обработку отправленных данных.

```blade
{{-- Вся форма — со всеми полями, подписями и кнопкой «Сохранить» — генерируется одной строкой --}}
<x-formster::form :object="$user" action="{{ route('users.update', $user) }}" />
```

```php
// Обработка отправки формы — тоже одна строка
Route::put('/users/{user}', function (Request $request, User $user) {
    ActionHandler::update($request, $user)->save();

    return back();
});
```

---

## Содержание

- [Возможности](#возможности)
- [Требования](#требования)
- [Установка](#установка)
- [Быстрый старт](#быстрый-старт)
- [Как это работает](#как-это-работает)
- [Описание свойств модели](#описание-свойств-модели)
- [Парсеры свойств](#парсеры-свойств)
- [Обработчики свойств (Handlers)](#обработчики-свойств-handlers)
- [Поддерживаемые типы и виджеты](#поддерживаемые-типы-и-виджеты)
- [Псевдотипы и касты](#псевдотипы-и-касты)
  - [Color — цвет](#color--цвет)
  - [DateTimeZone — часовой пояс](#datetimezone--часовой-пояс)
  - [File — файл](#file--файл)
  - [Image — изображение](#image--изображение)
- [Blade-компоненты](#blade-компоненты)
- [Контроль доступа (политики)](#контроль-доступа-политики)
- [Локализация](#локализация)
- [Алиасы](#алиасы)
- [Конфигурация](#конфигурация)
- [Создание собственного обработчика](#создание-собственного-обработчика)
- [Очистка осиротевших файлов](#очистка-осиротевших-файлов)
- [Фасады и публичный API](#фасады-и-публичный-api)
- [Тестирование и качество кода](#тестирование-и-качество-кода)
- [Лицензия](#лицензия)

---

## Возможности

- 🚀 **Формы без шаблонов.** Объявите модель — Formster сам построит редактируемую форму или таблицу-просмотр.
- 🧠 **Несколько источников метаданных.** Свойства извлекаются из PHPDoc (`@property`), нативных типов PHP (рефлексия) и PHP-атрибутов `#[Aura]` / `#[AuraProperty]`. Источники можно комбинировать.
- 🧩 **Богатая система типов.** Поддержка union (`A|B`), intersection (`A&B`), nullable, дженериков (`Collection<int, User>`, `list<File>`, `class-string<User>`) и рекурсивного разбора вложенных классов.
- 🎛️ **Готовые виджеты** для строк, чисел, дробных, булевых, enum, дат, часовых поясов, цветов, файлов и изображений.
- 🖼️ **Псевдотипы файлов и изображений** с загрузкой через `Storage`, автоматическими превью (Intervention Image) и очисткой старых файлов.
- 🔒 **Интеграция с Laravel Gate.** Просмотр и редактирование каждого свойства управляются политиками (`viewPolicy` / `updatePolicy`), с мягким режимом по умолчанию и переключаемым строгим режимом.
- 🌍 **Локализация** подписей полей, описаний и enum-значений (из коробки английский и русский).
- ⚡ **Кэширование** результатов парсинга.
- 🛠️ **Расширяемость** — собственные обработчики типов генерируются Artisan-командой.

---

## Требования

- PHP **8.2+** (тестируется на 8.2–8.5)
- Laravel **^12.17 || ^13.0**
- `intervention/image-laravel` `^1.5 || ^4.0` (для превью изображений)

---

## Установка

```bash
composer require ttbooking/formster
```

Пакет использует автообнаружение, поэтому сервис-провайдер и фасады регистрируются автоматически.

При необходимости опубликуйте конфигурацию и/или шаблоны представлений:

```bash
# конфигурация
php artisan vendor:publish --tag=formster-config

# Blade-шаблоны виджетов (для кастомизации вёрстки)
php artisan vendor:publish --tag=formster-views
```

---

## Быстрый старт

### 1. Опишите модель

Минимально достаточно PHPDoc-аннотаций `@property`. Никаких `$fillable`, кастов или ручного объявления полей формы не требуется — тип в аннотации определяет виджет.

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

### 2. Отрендерите форму или таблицу

```blade
{{-- Редактируемая форма (метод PUT, кнопка «Сохранить») --}}
<x-formster::form :object="$model" action="{{ route('update', $model) }}" />

{{-- Таблица только для просмотра --}}
<x-formster::form.table :object="$model" />
```

### 3. Обработайте отправку

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

`ActionHandler::update()` сам обходит все свойства модели, применяет нужный обработчик к каждому полю из запроса, учитывает политики доступа и возвращает изменённый объект — остаётся лишь вызвать `->save()`.

---

## Как это работает

Полный цикл «модель → форма → отправка» состоит из трёх этапов.

```
                ┌─────────────────────┐
   объект  ───► │   PropertyParser    │ ──► Aura { properties: AuraProperty[] }
                │  (phpstan,reflection)│
                └─────────────────────┘
                           │
                           ▼  для каждого свойства
                ┌─────────────────────┐
                │   HandlerFactory    │ ──► PropertyHandler (по типу свойства)
                └─────────────────────┘
                           │
            ┌──────────────┴───────────────┐
            ▼                              ▼
   component() → Blade-виджет     handle($obj, $request) → запись значения
   (рендеринг формы)              (обработка отправки)
```

1. **Парсинг.** `PropertyParser::parse($object)` разбирает объект и возвращает агрегат **`Aura`** — описание класса со списком свойств **`AuraProperty`** (имя, тип, читаемость/записываемость, значение по умолчанию, политики доступа).
2. **Подбор обработчика.** Для каждого свойства `HandlerFactory::for($property)` подбирает первый `PropertyHandler`, чей статический метод `satisfies()` соответствует типу свойства.
3. **Рендеринг и обработка.** При выводе формы обработчик через `component()` указывает Blade-виджет. При отправке `handle()` приводит значение из `Request` и записывает в объект.

### Ключевые сущности

| Сущность           | Назначение                                                                                                                                                                                                                               |
| ------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`Aura`**         | «Аура» класса: краткое (`summary`) и полное (`description`) описание, коллекция свойств (проиндексирована по имени) и политики по умолчанию (`viewPolicy`, `updatePolicy`). Одновременно является PHP-атрибутом уровня класса `#[Aura]`. |
| **`AuraProperty`** | Описание одного свойства: `readable`, `writable`, `type`, `variableName`, `description`, `hasDefaultValue`/`defaultValue`, `viewPolicy`, `updatePolicy`. Одновременно является атрибутом свойства `#[AuraProperty]`.                     |
| **`AuraType`**     | Система типов: `AuraNamedType` (именованный/дженерик-тип), `AuraUnionType` (`A\|B`), `AuraIntersectionType` (`A&B`). Предоставляет метод `contains()` и флаг `nullable`.                                                                 |

---

## Описание свойств модели

Formster поддерживает **три способа** объявить свойства, и их можно сочетать (см. [Парсеры](#парсеры-свойств)).

### Способ 1. PHPDoc-аннотации (рекомендуется)

```php
/**
 * @property string $name              Имя пользователя
 * @property int $age
 * @property ?string $bio              Может быть null
 * @property \App\Enums\Status $status
 * @property-read int $id              Только для чтения
 * @property-write string $password    Только для записи
 */
class User extends Model {}
```

- `@property` — свойство доступно и для чтения, и для записи;
- `@property-read` — только чтение (в форме отображается, но не редактируется);
- `@property-write` — только запись.

Текст после типа и имени становится описанием поля.

### Способ 2. Нативные типы PHP (рефлексия)

```php
class Dto
{
    public string $name;
    public int $age = 18;            // значение по умолчанию подхватывается автоматически
    public readonly string $id;      // readonly → только для чтения
    public ?Color $color = null;
}
```

### Способ 3. PHP-атрибуты

Для полного контроля над метаданными можно навесить атрибуты `#[Aura]` и `#[AuraProperty]` напрямую:

```php
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraProperty;
use TTBooking\Formster\Entities\AuraNamedType;

#[Aura(summary: 'Профиль', description: 'Данные пользователя')]
class Profile
{
    #[AuraProperty(
        readable: true,
        writable: true,
        type: new AuraNamedType('string'),
        variableName: 'nickname',
        description: 'Псевдоним',
    )]
    public string $nickname;
}
```

---

## Парсеры свойств

За извлечение метаданных отвечают парсеры. Активные парсеры и их порядок задаются опцией `formster.property_parser` (по умолчанию `phpstan,reflection`).

| Драйвер                | Источник данных                                                                                                           |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `aura`                 | PHP-атрибуты `#[Aura]` / `#[AuraProperty]`                                                                                |
| `reflection`           | Нативные типизированные `public`-свойства                                                                                 |
| `phpdoc`               | PHPDoc-блок класса через `phpdocumentor/reflection-docblock`                                                              |
| `phpstan`              | PHPDoc через `phpstan/phpdoc-parser` — поддерживает дженерики, const-выражения и **рекурсивный разбор вложенных классов** |
| `aggregate`            | Композит: объединяет несколько парсеров                                                                                   |
| (внутренний) `caching` | Декоратор, кэширующий результат любого парсера                                                                            |

### Агрегация

Если в `property_parser` указано несколько драйверов через запятую, автоматически используется драйвер `aggregate`. Он последовательно прогоняет объект через каждый парсер и **сливает** результаты через `Aura::merge()`.

> **Порядок важен:** парсеры, идущие позже в списке, имеют приоритет — их непустые значения перекрывают данные предыдущих. Например, при `phpstan,reflection` данные из PHPDoc дополняются и при совпадении перекрываются информацией из нативных типов.

### Кэширование

Результат парсинга кэшируется автоматически (декоратор `CachingParser`). Хранилище и TTL настраиваются через `formster.property_cache`. Ключ кэша: `formster:properties:{драйвер}:{класс}`.

---

## Обработчики свойств (Handlers)

Обработчик (`PropertyHandler`) связывает тип свойства с виджетом и логикой записи. Контракт:

```php
interface PropertyHandler
{
    public static function satisfies(AuraProperty $property): bool; // подходит ли тип
    public function component(): string;                            // Blade-виджет
    public function handle(object $object, Request $request): void; // запись значения
    public function validate(Request $request): bool;               // валидация
}
```

`HandlerFactory::for($property)` перебирает обработчики из конфига `formster.property_handlers` и возвращает первый, для которого `satisfies()` вернул `true`. Если ни один не подошёл, используется `FallbackHandler`.

---

## Поддерживаемые типы и виджеты

| Тип свойства          | Обработчик            | Виджет (Blade)               | HTML-поле                           |
| --------------------- | --------------------- | ---------------------------- | ----------------------------------- |
| `bool`                | `BooleanHandler`      | `form.checkbox`              | `<input type="checkbox">`           |
| `int`                 | `IntegerHandler`      | `form.number`                | `<input type="number">`             |
| `float`               | `FloatHandler`        | `form.decimal`               | `<input type="number" step="0.01">` |
| `string`              | `StringHandler`       | `form.text`                  | `<input type="text">`               |
| `BackedEnum`          | `EnumHandler`         | `form.radio` / `form.select` | переключатели или выпадающий список |
| `DateTimeInterface`   | `DateTimeHandler`     | `form.datetime`              | `<input type="datetime-local">`     |
| `DateTimeZone`        | `DateTimeZoneHandler` | `form.timezone`              | `<select>` с часовыми поясами       |
| `Color`               | `ColorHandler`        | `form.color`                 | `<input type="color">`              |
| `File` / `list<File>` | `FileHandler`         | `form.file`                  | `<input type="file">`               |
| `Image`               | `ImageHandler`        | `form.image`                 | `<input type="file">` + превью      |
| *прочее*              | `FallbackHandler`     | `form.disclaimer`            | сообщение «тип не поддерживается»   |

**Enum.** `EnumHandler` рендерит переключатели (`radio`), если число вариантов не превышает порог `buttonLimit` (по умолчанию **2**), и выпадающий список (`select`) в противном случае.

Описания вариантов enum локализуются (см. [Локализация](#локализация)); при отсутствии перевода берётся PHPDoc-комментарий кейса или его «человекочитаемое» имя.

---

## Псевдотипы и касты

Помимо скалярных типов Formster предоставляет четыре **псевдотипа** в пространстве `TTBooking\Formster\Types`. Они реализуют `Castable`, поэтому достаточно объявить их в `casts()` модели — Eloquent сам подберёт нужный каст, а Formster выведет соответствующий виджет.

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

### Color — цвет

HEX-цвет в формате `#RRGGBB`. Рендерится как `<input type="color">`, в режиме просмотра — цветная плашка.

```php
$product->brand_color = new Color('#3366ff');
```

Конструктор валидирует формат (`/^#[a-zA-Z0-9]{6}$/`) и при ошибке бросает `InvalidArgumentException`.

### DateTimeZone — часовой пояс

Расширяет нативный `\DateTimeZone`, рендерится в `<select>`, сгруппированный по регионам. Группой можно управлять через параметры псевдотипа в аннотации:

```php
/**
 * Все пояса, сгруппированные по регионам (по умолчанию):
 * @property ?DateTimeZone $tz
 *
 * Только пояса России (двухбуквенный ISO-код страны):
 * @property ?DateTimeZone<'RU'> $tz_ru
 */
```

### File — файл

Загрузка файла через `Storage`. В режиме редактирования — `<input type="file">`, в режиме просмотра — ссылка на скачивание/открытие.

Параметры псевдотипа задаются дженерик-аннотацией: `File<TAccept, TDisposition, TDisk>`.

```php
/**
 * PDF-документы, заголовок «attachment» (скачивание), диск «documents»:
 * @property ?File<'application/pdf', 'attachment', 'documents'> $contract
 *
 * Несколько файлов (поле получит атрибут multiple):
 * @property list<File> $attachments
 */
```

- `TAccept` — фильтр MIME-типов для атрибута `accept` (по умолчанию `*/*`);
- `TDisposition` — `attachment` (скачивание) или `inline` (открытие в браузере);
- `TDisk` — диск файловой системы (по умолчанию из конфига).

**Имена сохраняемых файлов.** По умолчанию используется `hashName()`. Логику можно переопределить глобально, например в `boot()` сервис-провайдера:

```php
use TTBooking\Formster\Types\File;

File::generateStorableNamesUsing(function ($object, $property, $uploadedFile, $disk) {
    return 'uploads/'.$uploadedFile->getClientOriginalName();
});

// вернуть поведение по умолчанию:
File::generateStorableNamesNormally();
```

При загрузке нового файла старый автоматически удаляется (если он не «статический» и не совпадает со значением по умолчанию). Статическими считаются файлы, чьё имя начинается с `/` — они хранятся на отдельном `static_disk` и не удаляются.

### Image — изображение

Наследует `File`, но по умолчанию принимает `image/*`, открывается `inline` и **показывает превью**.

Превью генерируется через **Intervention Image**: изображение уменьшается до размеров `formster.preview.width × height`. SVG и файлы меньше порога `scale_down_threshold` отдаются как есть. Поддерживаются обе мажорные версии Intervention Image.

---

## Blade-компоненты

Все компоненты доступны в пространстве имён `formster::`.

### Структурные компоненты

| Компонент                  | Назначение                                                         | Основные параметры                                 |
| -------------------------- | ------------------------------------------------------------------ | -------------------------------------------------- |
| `<x-formster::form>`       | Полноценная `<form>` (POST + `@method('PUT')`, кнопка «Сохранить») | `:object`, `action`, `:show-defaults`              |
| `<x-formster::form.table>` | Таблица свойств (просмотр или редактирование)                      | `:object`, `action`, `:editable`, `:show-defaults` |
| `<x-formster::form.row>`   | Строка таблицы для одного свойства                                 | `:property`                                        |
| `<x-formster::form.input>` | Виджет ввода для свойства (выбирает компонент через обработчик)    | `:property`, `:object`                             |

**Примеры:**

```blade
{{-- Готовая форма с кнопкой «Сохранить» --}}
<x-formster::form :object="$model" action="{{ route('users.update', $model) }}" />

{{-- Только таблица, без столбца значений по умолчанию --}}
<x-formster::form.table :object="$model" :editable="false" :show-defaults="false" />

{{-- Своя кнопка вместо стандартной (через слот) --}}
<x-formster::form :object="$model" action="{{ route('users.update', $model) }}">
    <x-slot:buttons>
        <button type="submit">Обновить профиль</button>
    </x-slot:buttons>
</x-formster::form>
```

> Если среди свойств есть файл или изображение, форма автоматически получает `enctype="multipart/form-data"`.

### Компоненты-виджеты (анонимные)

Каждый виджет можно вызвать и напрямую: `form.text`, `form.number`, `form.decimal`, `form.checkbox`, `form.radio`, `form.select`, `form.datetime`, `form.color`, `form.timezone`, `form.file`, `form.image`, `form.disclaimer`.

```blade
<x-formster::form.text :property="$property" />
```

Виджеты используют `@aware` для наследования контекста (`object`, `editable`, `action`) от родительской таблицы и показывают либо редактируемое поле, либо представление «только для чтения».

Чтобы изменить вёрстку, опубликуйте шаблоны (`vendor:publish --tag=formster-views`) и отредактируйте файлы в `resources/views/vendor/formster`.

---

## Контроль доступа (политики)

Видимость и редактируемость каждого свойства проверяются через **Laravel Gate**. У `Aura` и `AuraProperty` есть политики:

- `viewPolicy` (по умолчанию `view`) — можно ли **показывать** свойство;
- `updatePolicy` (по умолчанию `update`) — можно ли **редактировать** свойство.

При рендеринге таблицы и при обработке отправки Formster вызывает соответствующий метод политики модели, передавая модель и имя свойства:

```php
class UserPolicy
{
    // $property — имя свойства, например 'email'
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

### Мягкий режим (lenient) — по умолчанию

По умолчанию Formster работает в «щадящем» режиме: доступ предоставляется автоматически, если

- для объекта **не определена** политика, **или**
- в политике **отсутствует метод** под проверяемую способность.

Это позволяет пользоваться Formster без написания политик, добавляя ограничения постепенно — только там, где они нужны.

Реализовано через `Gate::before()`-колбэк `TTBooking\Formster\Support\LenientPolicy`, который регистрируется в сервис-провайдере. Колбэк вмешивается **только** для объектов, помеченных атрибутом `#[Aura]`, и лишь при отсутствии политики/метода: в этом случае он возвращает `true` (полный доступ). Во всех остальных случаях он возвращает `null` и передаёт управление стандартной проверке `Gate::check()`. Объектов без `#[Aura]` он не касается вовсе.

### Строгий режим (enforce)

Если требуется обычное поведение Laravel Gate (нет политики/метода → доступ **запрещён**), включите строгий режим:

```dotenv
FORMSTER_ENFORCE_POLICIES=true
```

или в `config/formster.php`:

```php
'enforce_policies' => true,
```

При `enforce_policies = true` колбэк `LenientPolicy` не регистрируется, и результат каждой проверки полностью определяется вашими политиками через `Gate::check()`.

---

## Локализация

Подписи интерфейса и описания свойств переводятся. Пакет поставляется с английскими и русскими строками; их можно опубликовать и дополнить.

### Подписи интерфейса

Файл `lang/vendor/formster/{locale}/form.php`:

| Ключ                             | RU                           |
| -------------------------------- | ---------------------------- |
| `description`                    | Параметр                     |
| `value`                          | Значение                     |
| `default`                        | По умолч.                    |
| `na`                             | н/д                          |
| `null`                           | NULL                         |
| `on` / `off`                     | ✔️ / ❌                      |
| `open` / `download` / `uploaded` | открыть / скачать / загружен |
| `save`                           | Сохранить                    |

### Описания свойств и enum-значений

Описание поля ищется по ключам перевода (с приоритетом строк приложения над пакетными):

```
formster.{model|object}.{alias}.{свойство_в_snake_case}
```

Для enum-значений:

```
formster.enum.{alias}.{кейс_в_snake_case}
```

Например, для модели `App\Models\User` и свойства `firstName`:

```php
// lang/{locale}/formster.php
return [
    'model' => [
        'user' => [
            'first_name' => 'Имя',
            '_summary'     => 'Профиль пользователя',   // заголовок таблицы
            '_description' => 'Основные данные',         // описание таблицы
        ],
    ],
];
```

Если перевод не найден, описание берётся из текста PHPDoc-аннотации, а в крайнем случае генерируется из имени свойства (`Str::headline`).

---

## Алиасы

`alias` — это ключ, под которым модель/enum фигурирует в строках локализации. По умолчанию он вычисляется из имени класса (с отбрасыванием namespace `App\Models\` / `App\Enums\` и переводом остатка в `snake_case`). Зафиксировать собственный алиас можно атрибутом `#[Alias]`:

```php
use TTBooking\Formster\Attributes\Alias;

#[Alias('user')]
class Customer extends Model {}
```

---

## Конфигурация

После публикации (`vendor:publish --tag=formster-config`) доступен файл `config/formster.php`:

```php
return [

    // Парсер(ы) свойств. Несколько — через запятую (включает агрегацию).
    'property_parser' => env('FORMSTER_PROPERTY_PARSER', 'phpstan,reflection'),

    // Кэш результатов парсинга.
    'property_cache' => [
        'store' => env('FORMSTER_PROPERTY_CACHE_STORE'),      // хранилище кэша (по умолчанию — стандартное)
        'ttl'   => (int) env('FORMSTER_PROPERTY_CACHE_TTL') ?: null, // время жизни (null — бессрочно)
    ],

    // Активные обработчики свойств (порядок = приоритет проверки satisfies()).
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

    // Строгий режим политик. false — мягкий режим (доступ при отсутствии
    // политики/метода), true — обычное поведение Laravel Gate.
    'enforce_policies' => (bool) env('FORMSTER_ENFORCE_POLICIES', false),

    // Настройки псевдотипа File.
    'file' => [
        'disk'                => env('FORMSTER_DISK'),                         // диск для загрузок
        'static_disk'         => env('FORMSTER_STATIC_DISK', env('FORMSTER_DISK')), // диск для статических файлов
        'content_disposition' => env('FORMSTER_CONTENT_DISPOSITION', 'attachment'),
        'show_uploaded_name'  => (bool) env('FORMSTER_SHOW_FILENAME', true),   // показывать имя файла в ссылке
    ],

    // Настройки превью для псевдотипа Image.
    'preview' => [
        'width'                => (int) env('FORMSTER_PREVIEW_WIDTH', 100),
        'height'               => (int) env('FORMSTER_PREVIEW_HEIGHT', 100),
        'scale_down_threshold' => (int) env('FORMSTER_PREVIEW_SCALE_DOWN_THRESHOLD', 10_240), // байт
    ],

];
```

### Переменные окружения

| Переменная                              | Назначение                  | По умолчанию         |
| --------------------------------------- | --------------------------- | -------------------- |
| `FORMSTER_PROPERTY_PARSER`              | Парсер(ы) свойств           | `phpstan,reflection` |
| `FORMSTER_ENFORCE_POLICIES`             | Строгий режим политик       | `false`              |
| `FORMSTER_PROPERTY_CACHE_STORE`         | Хранилище кэша              | стандартное          |
| `FORMSTER_PROPERTY_CACHE_TTL`           | TTL кэша (сек)              | бессрочно            |
| `FORMSTER_DISK`                         | Диск для загрузок           | диск по умолчанию    |
| `FORMSTER_STATIC_DISK`                  | Диск для статических файлов | `FORMSTER_DISK`      |
| `FORMSTER_CONTENT_DISPOSITION`          | Поведение файлов            | `attachment`         |
| `FORMSTER_SHOW_FILENAME`                | Показывать имя файла        | `true`               |
| `FORMSTER_PREVIEW_WIDTH` / `_HEIGHT`    | Размер превью               | `100` / `100`        |
| `FORMSTER_PREVIEW_SCALE_DOWN_THRESHOLD` | Порог уменьшения превью     | `10240`              |

---

## Создание собственного обработчика

Чтобы добавить поддержку нового типа, сгенерируйте обработчик командой:

```bash
php artisan make:formster-handler MoneyHandler --type=Money
```

- `--type` (`-t`) — обрабатываемый тип или класс;
- `--force` (`-f`) — перезаписать существующий класс.

Команда интерактивна: при отсутствии аргументов спросит имя и предложит выбрать тип из каталога `app/Formster/Types`. Класс создаётся в namespace `App\Formster\Handlers`.

Сгенерированный обработчик:

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

Зарегистрируйте обработчик в `config/formster.php` (в массиве `property_handlers`, до `FileHandler`/`FallbackHandler`) и создайте Blade-виджет `form.money`.

Стаб генератора можно опубликовать и кастомизировать, поместив `stubs/handler.stub` в корень приложения.

---

## Очистка осиротевших файлов

Чтобы загруженные файлы удалялись при удалении модели, подключите обсервер `OrphanedFileCollector`:

```php
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use TTBooking\Formster\Observers\OrphanedFileCollector;

#[ObservedBy(OrphanedFileCollector::class)]
class Product extends Model {}
```

При удалении модели обсервер удаляет все привязанные файлы (`File`/`Image`), кроме статических (имя начинается с `/`). При **мягком** удалении (SoftDeletes без force-delete) файлы сохраняются.

---

## Фасады и публичный API

| Фасад             | Класс                   | Назначение                                                                               |
| ----------------- | ----------------------- | ---------------------------------------------------------------------------------------- |
| `PropertyParser`  | `PropertyParserManager` | `parse($objectOrClass): Aura` — разбор объекта/класса в метаданные                       |
| `PropertyHandler` | `HandlerFactory`        | `for(AuraProperty $property): PropertyHandler` — подбор обработчика                      |
| `ActionHandler`   | `ActionHandler`         | `update(Request $request, object $object): object` — применение данных запроса к объекту |

```php
use TTBooking\Formster\Facades\PropertyParser;

$aura = PropertyParser::parse(App\Models\User::class);

foreach ($aura->properties as $property) {
    echo $property->variableName.': '.$property->type.PHP_EOL;
}
```

---

## Тестирование и качество кода

Пакет использует **Pest**, **PHPStan (larastan, level max)** и **Laravel Pint**. Доступные composer-скрипты:

```bash
composer test      # запуск тестов (Pest)
composer analyse   # статический анализ (PHPStan)
composer lint      # проверка стиля (Pint --test)
composer serve     # запуск демо-приложения (workbench)
```

CI прогоняет матрицу PHP 8.2–8.5 × Laravel 12.17 / 13.0 (prefer-lowest и prefer-stable).

---

## Лицензия

Formster распространяется по лицензии **MIT**. Подробности — в файле [LICENSE.md](LICENSE.md).
