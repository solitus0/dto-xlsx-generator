# Tutorial · Generate Your First Spreadsheet

This guided walk-through helps you install the bundle, describe a DTO with attributes, and export an XLSX file. Follow the steps in order; each step builds on the previous one.

## 1. Instalaltion

```bash
composer require solitus0/dto-xlsx-generator
```

## 2. Register the bundle

Make sure the bundle is added to `config/bundles.php`:

```php
return [
    // ...
    Solitus0\DtoXlsxGenerator\DtoXlsxGenerator::class => ['all' => true],
];
```

The default `config/services.php` shipped with the bundle autowires every attribute resolver under the tag `solitus0.dto.attributes_resolver`, so no extra wiring is needed.

## 3. Describe your DTOs

Create a root DTO that represents the primary worksheet and annotate its properties. Inline collections stay on the same sheet; nested collections become dedicated tabs.

```php
use Solitus0\DtoXlsxGenerator\Attributes as XLSX;

#[XLSX\SpreadsheetRoot(ReportDto::class, sheetName: 'Reports')]
final class ReportDto
{
    #[XLSX\SpreadsheetProperty]
    public int $id;

    #[XLSX\SpreadsheetProperty]
    public string $title;

    #[XLSX\SpreadsheetVirtualProperty('status')]
    public function statusLabel(): string
    {
        return 'Published';
    }

    #[XLSX\SpreadsheetInlineCollection(className: ConsignorAddressDto::class)]
    public array $consignors = [];

    #[XLSX\SpreadsheetCollection(
        className: ReportLineDto::class,
        mappedBy: 'id',
        sheetName: 'Report Lines',
    )]
    public array $lines = [];
}
```

Child DTOs simply define their own `#[SpreadsheetProperty]` annotations. A separate root attribute is not required unless they are also exported independently.

## 4. Validate the DTO configuration

Validate once during deployment or inside a smoke test to catch missing attributes early:

```php
use Solitus0\DtoXlsxGenerator\Validator\DtoConfigurationValidator;

$validator = new DtoConfigurationValidator();
$errors = $validator->validate(ReportDto::class);

if ($errors !== []) {
    throw new \RuntimeException(implode("\n", $errors));
}
```

## 5. Generate and save the spreadsheet

Inject the `SpreadsheetGenerator` service and pass it an iterable of DTOs. Wrap the resulting `Spreadsheet` in an `Xlsx` writer from the provided factory, then persist or stream it however you prefer.

```php
use Solitus0\DtoXlsxGenerator\Services\SpreadsheetGenerator;
use Solitus0\DtoXlsxGenerator\Factory\XlsxWriterFactory;

final class ExportReportsAction
{
    public function __construct(private SpreadsheetGenerator $generator) {}

    public function __invoke(): void
    {
        $reports = $this->repository->findLatest();
        $spreadsheet = $this->generator->generate($reports);

        $writer = XlsxWriterFactory::create($spreadsheet);
        $tmpPath = tempnam(sys_get_temp_dir(), 'reports_xlsx_');
        $writer->save($tmpPath);
        // send the file or move it wherever you need
    }
}
```

## 6. Next steps

- Swap the inline collection for a dedicated worksheet using the [Model collections how-to](../how-to/model-collections.md).
- Integrate validation into CI using the [Validate DTOs how-to](../how-to/validate-dtos.md).
- Learn how the attribute resolvers work internally in the [Design notes explanation](../explanations/design-philosophy.md).
