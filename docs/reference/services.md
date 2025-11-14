# Reference · Services & Configuration

All services live in the `Solitus0\DtoXlsxGenerator` namespace and are autoconfigured via the bundle’s `config/services.php`.

## SpreadsheetGenerator

- **Service id:** `Solitus0\DtoXlsxGenerator\Services\SpreadsheetGenerator`
- **Constructor:** `__construct(iterable $resolvers)` – tagged resolvers are injected via `tagged_iterator('solitus0.dto.attributes_resolver')`.
- **Usage:** Call `generate(iterable $objects, ?Spreadsheet $spreadsheet = null): Spreadsheet`.
  - When the `$spreadsheet` argument is omitted, a new PhpSpreadsheet instance is created and its default empty sheet is removed.
  - Resolvers are sorted by priority on every call and reset their caches before processing.

## XlsxWriterFactory

- **Type:** static factory with `create(Spreadsheet $spreadsheet): Xlsx`.
- **Use cases:** Keep writer creation consistent across controllers, console commands, or queue workers.

## DtoConfigurationValidator

- **Service id:** aliased as `solitus0.dto_configuration_validator`.
- **Responsibilities:**
  - Ensure DTO classes exist and contain at least one spreadsheet attribute.
  - Detect duplicate worksheet names.
  - Validate nested `#[SpreadsheetCollection]` and `#[SpreadsheetInlineCollection]` targets recursively.
- **API:** `validate(string $dtoClass): string[]` returns an array of error messages (empty array indicates success).

## Service tags

| Tag | Applied to | Purpose |
| --- | --- | --- |
| `solitus0.dto.attributes_resolver` | Classes implementing `SpreadsheetResolverInterface` | Registers attribute resolvers (root, worksheets, collections) so the generator can iterate over them.

### Built-in resolvers

| Class | Priority | Role |
| --- | --- | --- |
| `SpreadsheetRootResolver` | Highest | Creates worksheets for root DTOs. |
| `SpreadsheetCollectionResolver` | Medium | Expands `#[SpreadsheetCollection]` definitions. |
| `WorksheetsResolver` | Lower | Writes headers and row values, including inline collections and virtual properties. |

Custom resolvers can hook into the same tag to enrich spreadsheets with domain-specific metadata (e.g., styles, formulas). Implement `resetInMemoryCache()` to clear any state between generator runs.
