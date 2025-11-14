<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Factory;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxWriterFactory
{
    public static function create(Spreadsheet $spreadsheet): Xlsx
    {
        return new Xlsx($spreadsheet);
    }
}
