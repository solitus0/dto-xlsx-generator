# Repository Guidelines

## Project Structure & Module Organization
Bundle code lives in `src/` under the `Solitus0\\DtoXlsxGenerator` namespace, with the primary entry point at `src/DtoXlsxGenerator.php`. Dependency wiring, tagged services, and container parameters belong in `config/services.php`. PHPUnit specs mirror the source tree inside `tests/`, while reusable docs, RFCs, and diagrams go in `docs/`. The `report-xlsx-generator/` folder hosts a self-contained demo app; use it for integration experiments without polluting bundle classes.

## Build, Test, and Development Commands
Run `composer install` in the repo root (and again inside `report-xlsx-generator/` when iterating on the demo) to install dependencies. Refresh autoload metadata with `composer dump-autoload -o` after adding or renaming classes. Execute the bundle test suite via `vendor/bin/phpunit` or narrow with `vendor/bin/phpunit tests/Dto`. For static analysis, prefer `php -d detect_unicode=0 vendor/bin/phpstan analyse src` at level 8.

## Coding Style & Naming Conventions
Follow PSR-12: four-space indentation, one class per file, brace-on-new-line. Namespace bundle services under `Solitus0\\DtoXlsxGenerator\\*`; demo helpers stay under `MyVendor\\ReportXlsxGenerator\\*`. Use descriptive suffixes such as `*Mapper`, `*Writer`, or `*Test`, and reserve snake_case for config keys only. Before committing, run your formatter (e.g., `php-cs-fixer fix --dry-run`) to ensure no style drift.

## Testing Guidelines
Tests live in `tests/` (or `report-xlsx-generator/tests/` for demo fixtures) and must end with `Test.php`. Favor PHPUnit 9.x with data providers for DTO tables, and mock external I/O (filesystem, PhpSpreadsheet writers). Target ≥80% line coverage for new features, and add regression tests when fixing bugs. Document non-trivial fixtures in `docs/TESTING.md`.

## Commit & Pull Request Guidelines
Use Conventional Commits, e.g., `feat(bundle): add column autofit` or `fix: handle empty collections`, keeping the subject ≤72 characters. Every PR should link to the relevant issue or discussion, summarize behavior changes, list manual test steps, and attach artifacts (GIF, XLSX sample) when export output changes. Rebase atop `main` before requesting review and ensure PHPUnit (and PHPStan, if configured) pass locally.

## Security & Configuration Tips
Never hardcode credentials; surface configuration through the host app’s `env()` parameters and wire them in `config/services.php`. When debugging spreadsheets, stash temporary XLSX files in `/tmp/dtoxlsx` (gitignored) and purge after review. If you introduce new third-party libraries, update `composer.json` and note licensing implications inside `docs/SECURITY.md`.
