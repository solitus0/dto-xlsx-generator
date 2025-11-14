# Reference · Attributes

Each attribute lives under `Solitus0\DtoXlsxGenerator\Attributes`. Combine them to describe how DTOs should be rendered into worksheets.

## `#[SpreadsheetRoot]`

- **Target:** class
- **Arguments:**
  - `className` (string, required) – DTO class name used for metadata caches.
  - `sheetName` (string, optional) – overrides the humanised class name.
- **Behavior:** Marks the DTO whose instances seed worksheets. Automatically creates (or selects) a sheet and sets the root depth to `0`.

```php
#[SpreadsheetRoot(ReportDto::class, sheetName: 'Reports')]
final class ReportDto {}
```

## `#[SpreadsheetProperty]`

- **Target:** property
- **Arguments:** `cellValueGetter` (string, optional) – method on the property value used to derive display text.
- **Behavior:** Adds a column whose header defaults to the property name. Handles `DateTimeInterface`, backed enums, arrays, and strings out of the box. If used on object cellValueGetter argument must be specified.

```php
#[SpreadsheetProperty(cellValueGetter: 'getFullName')]
private Recipient $name;
```

## `#[SpreadsheetVirtualProperty]`

- **Target:** method
- **Arguments:** `columnName` (string, required).
- **Behavior:** Exposes computed values without storing them as state.

```php
#[SpreadsheetVirtualProperty('statusLabel')]
public function statusLabel(): string {}
```

## `#[SpreadsheetInlineCollection]`

- **Target:** property representing an array, `Collection`, or iterable of DTOs.
- **Arguments:** `className` (string, required) – the DTO type of the collection elements.
- **Behavior:** Serializes every element into newline-delimited `key: value` chunks inside a single column.

```php
#[SpreadsheetInlineCollection(className: ConsignorDto::class)]
private array $consignors = [];
```

## `#[SpreadsheetCollection]`

- **Target:** property representing a collection of DTOs.
- **Arguments:**
  - `className` (string, required).
  - `mappedBy` (string, required) – parent property whose value is copied into column `A` of the child worksheet.
  - `sheetName` (string, optional) – overrides the humanised class name.
- **Behavior:** Creates a worksheet dedicated to the child DTO type.

```php
#[SpreadsheetCollection(
    className: ReportLineDto::class,
    mappedBy: 'id',
    sheetName: 'Lines'
)]
private array $lines = [];
```
