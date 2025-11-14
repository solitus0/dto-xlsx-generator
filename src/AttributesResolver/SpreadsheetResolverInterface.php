<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\AttributesResolver;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface SpreadsheetResolverInterface
{
    public static function getPriority(): int;

    public function resolve(Spreadsheet $spreadsheet, iterable $objects): void;

    public function resetInMemoryCache(): void;
}
