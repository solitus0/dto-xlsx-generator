<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Unit\AttributesResolver;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Solitus0\DtoXlsxGenerator\Attributes\WorksheetInterface;
use Solitus0\DtoXlsxGenerator\AttributesResolver\AbstractSpreadsheetResolver;

class TestSpreadsheetResolver extends AbstractSpreadsheetResolver
{
    public static function getPriority(): int
    {
        return 1;
    }

    public function resolve(Spreadsheet $spreadsheet, iterable $objects): void
    {
    }

    public function getSpreadsheetProperties(
        string $className,
        object $object,
        string $mappedBy = null,
        object $parentEntity = null,
        ?\ReflectionProperty $collectionProperty = null,
    ): array {
        return parent::getSpreadsheetProperties($className, $object, $mappedBy, $parentEntity, $collectionProperty);
    }

    public function getPropertiesWithAttribute(string $objectClass, string $attributeClass): array
    {
        return parent::getPropertiesWithAttribute($objectClass, $attributeClass);
    }

    public function getAlphabeticColumnIndex(int $index): string
    {
        return parent::getAlphabeticColumnIndex($index);
    }

    public function getClassName(iterable $objects): ?string
    {
        return parent::getClassName($objects);
    }

    public function createWorksheet(
        Spreadsheet $spreadsheet,
        WorksheetInterface $attribute,
    ): Worksheet {
        return parent::createWorksheet($spreadsheet, $attribute);
    }

    public function writeRows(Worksheet $sheet, array $rowsData): void
    {
        parent::writeRows($sheet, $rowsData);
    }

    public function getVirtualProperties(string $objectClass)
    {
        return parent::getVirtualProperties($objectClass);
    }

    public function getWorksheet(Spreadsheet $spreadsheet, WorksheetInterface $attribute): Worksheet
    {
        return parent::getWorksheet($spreadsheet, $attribute);
    }

    public function getMethodsWithAttribute(string $objectClass, string $attributeClass): array
    {
        return parent::getMethodsWithAttribute($objectClass, $attributeClass);
    }
}
