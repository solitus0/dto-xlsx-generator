# How-to · Validate DTO Schemas

Use the built-in `DtoConfigurationValidator` to stop malformed DTO graphs before they reach production.

## When to run validation

- During unit tests for every DTO-rich feature.
- As a CI smoke test to catch refactors that remove required attributes.
- In a deployment checklist before tagging a release.

## Standalone usage

```php
use Solitus0\DtoXlsxGenerator\Validator\DtoConfigurationValidator;

$validator = new DtoConfigurationValidator();
$errors = $validator->validate(ReportDto::class);

if ($errors !== []) {
    throw new \RuntimeException("Invalid spreadsheet DTO:\n" . implode("\n", $errors));
}
```

Common failures the validator reports:

- Missing `#[SpreadsheetRoot]` on the entry DTO.
- DTOs with zero mapped properties or methods.
- Duplicate sheet names across `#[SpreadsheetCollection]` attributes.
- Collections targeting DTO classes that do not exist or lack spreadsheet annotations.

## Wiring it into Symfony

The bundle registers an alias named `solitus0.dto_configuration_validator`. Inject it where needed:

```php
class ReportExportReadinessCheck
{
    public function __construct(private DtoConfigurationValidator $validator) {}

    public function __invoke(): void
    {
        $errors = $this->validator->validate(ReportDto::class);
        if ($errors) {
            throw new ExportConfigurationException($errors);
        }
    }
}
```

## Automating with PHPUnit

```php
use PHPUnit\Framework\TestCase;
use Solitus0\DtoXlsxGenerator\Validator\DtoConfigurationValidator;

final class SpreadsheetMappingTest extends TestCase
{
    public function test_report_mapping_is_valid(): void
    {
        $validator = new DtoConfigurationValidator();
        self::assertSame([], $validator->validate(ReportDto::class));
    }
}
```

Treat this as a regression test: once a DTO is valid, any future schema drift will fail fast.
