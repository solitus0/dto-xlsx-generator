<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\AttributesResolver;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Attributes\WorksheetInterface;
use Solitus0\DtoXlsxGenerator\Util\ArrayPropertyUtil;
use Solitus0\DtoXlsxGenerator\Util\AttributeUtil;

class WorksheetsResolver extends AbstractSpreadsheetResolver
{
    public static function getPriority(): int
    {
        return 100;
    }

    public function resolve(Spreadsheet $spreadsheet, iterable $objects): void
    {
        $this->resetInMemoryCache();
        $className = $this->getClassName($objects);
        $rootAttribute = AttributeUtil::getClassAttribute($className, SpreadsheetRoot::class);
        if (!$rootAttribute instanceof SpreadsheetRoot) {
            return;
        }

        $this->worksheetAttributes[] = $rootAttribute;
        $this->addWorksheetChildrenAttributes($rootAttribute);
        $this->sortWorksheetAttributes();

        foreach ($this->worksheetAttributes as $worksheetAttribute) {
            if ($spreadsheet->getSheetByName($worksheetAttribute->getSheetName())) {
                continue;
            }

            $this->createWorksheet($spreadsheet, $worksheetAttribute);
        }
    }

    private function addWorksheetChildrenAttributes(WorksheetInterface $rootAttribute, int $depth = 1): void
    {
        $parentCollection = $this->getPropertiesWithAttribute(
            $rootAttribute->getClassName(),
            SpreadsheetCollection::class
        );

        /** @var \ReflectionProperty $collectionProperty */
        foreach ($parentCollection as $collectionProperty) {
            $attribute = AttributeUtil::getPropertyAttribute($collectionProperty, SpreadsheetCollection::class);
            if (!$attribute instanceof WorksheetInterface) {
                continue;
            }

            if ($attribute instanceof SpreadsheetCollection) {
                $attribute->setMappedByHeaderPrefix(
                    $this->humanizeClassName($collectionProperty->getDeclaringClass()->getShortName())
                );
            }

            $mappedBy = $attribute->getMappedBy();
            if (!ArrayPropertyUtil::getProperty($this->worksheetDepth, $mappedBy)) {
                $this->worksheetDepth[$mappedBy] = $depth;
                $depth++;
            }

            $this->worksheetAttributes[] = $attribute;

            $nestedCollections = $this->getPropertiesWithAttribute(
                $attribute->getClassName(),
                SpreadsheetCollection::class
            );

            if ($nestedCollections) {
                $this->addWorksheetChildrenAttributes($attribute, $depth);
            }
        }
    }

    private function sortWorksheetAttributes(): void
    {
        foreach ($this->worksheetAttributes as $worksheetAttribute) {
            $mappedBy = $worksheetAttribute->getMappedBy();
            if (!$mappedBy) {
                continue;
            }

            $worksheetAttribute->setDepth($this->worksheetDepth[$mappedBy]);
        }

        usort($this->worksheetAttributes, static function (WorksheetInterface $a, WorksheetInterface $b) {
            return $a->getDepth() <=> $b->getDepth();
        });

        foreach ($this->worksheetAttributes as $index => $worksheetAttribute) {
            $worksheetAttribute->setSheetIndex($index);
        }
    }

    private function humanizeClassName(string $className): string
    {
        return trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $className) ?? $className);
    }
}
