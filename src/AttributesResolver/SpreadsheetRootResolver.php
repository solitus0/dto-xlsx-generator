<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\AttributesResolver;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Util\AttributeUtil;

class SpreadsheetRootResolver extends AbstractSpreadsheetResolver
{
    public static function getPriority(): int
    {
        return WorksheetsResolver::getPriority() - 1;
    }

    public function resolve(Spreadsheet $spreadsheet, iterable $objects): void
    {
        $className = $this->getClassName($objects);
        $attribute = AttributeUtil::getClassAttribute($className, SpreadsheetRoot::class);
        if (!$attribute instanceof SpreadsheetRoot) {
            return;
        }

        $worksheet = $this->getWorksheet($spreadsheet, $attribute);
        $data = [];
        foreach ($objects as $object) {
            $data[] = $this->getSpreadsheetProperties($className, object: $object);
        }

        $this->writeRows($worksheet, $data);
    }
}
