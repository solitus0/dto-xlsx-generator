# DtoXlsxGenerator

[![Latest Stable Version](https://img.shields.io/packagist/v/solitus0/dto-xlsx-generator?style=flat-square)](https://packagist.org/packages/solitus0/dto-xlsx-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/solitus0/dto-xlsx-generator?style=flat-square)](https://packagist.org/packages/solitus0/dto-xlsx-generator)
[![License](https://img.shields.io/packagist/l/solitus0/dto-xlsx-generator?style=flat-square)](LICENSE)

A lightweight Symfony bundle that converts **attribute-decorated DTOs** into fully populated **XLSX spreadsheets** using PhpSpreadsheet.

## Features

- Attribute-first API: describe spreadsheet layouts directly on PHP DTOs.
- Inline and multi-sheet collection support with automatic parent-child linking.

## Quick Start

```bash
composer require solitus0/dto-xlsx-generator
```

Register the bundle in `config/bundles.php`, add spreadsheet attributes to your DTOs, then inject `Solitus0\DtoXlsxGenerator\Services\SpreadsheetGenerator` to build workbooks. The full walk-through lives in the [Generate Your First Spreadsheet tutorial](docs/tutorials/getting-started.md).

## Documentation (Diátaxis)

- [Tutorial](docs/tutorials/getting-started.md) – learn the workflow end-to-end.
- How-to guides: [model collections](docs/how-to/model-collections.md), [validate DTOs](docs/how-to/validate-dtos.md), [deliver XLSX files](docs/how-to/deliver-files.md).
- Reference: [attributes](docs/reference/attributes.md) and [services & configuration](docs/reference/services.md).
