<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\AttributesResolver;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Util\AttributeUtil;

class SpreadsheetCollectionResolver extends AbstractSpreadsheetResolver
{
    public static function getPriority(): int
    {
        return SpreadsheetRootResolver::getPriority() - 1;
    }

    public function resolve(Spreadsheet $spreadsheet, iterable $objects): void
    {
        $objectClass = $this->getClassName($objects);
        if (!$objectClass) {
            return;
        }

        $collections = $this->getPropertiesWithAttribute($objectClass, SpreadsheetCollection::class);
        if (!$collections) {
            return;
        }

        foreach ($collections as $collection) {
            $attribute = AttributeUtil::getPropertyAttribute($collection, SpreadsheetCollection::class);
            if (!$attribute instanceof SpreadsheetCollection) {
                continue;
            }

            $this->resolveCollection($collection, $objects, $attribute, $spreadsheet);
        }
    }

    private function resolveCollection(
        \ReflectionProperty $collection,
        iterable $parentEntities,
        SpreadsheetCollection $attribute,
        Spreadsheet $spreadsheet,
    ): void {
        $attribute->setMappedByHeaderPrefix(
            $this->humanizeClassName($collection->getDeclaringClass()->getShortName())
        );
        $worksheet = $this->getWorksheet($spreadsheet, $attribute);

        foreach ($parentEntities as $parentEntity) {
            $collectionItems = $collection->getValue($parentEntity);
            if ($collectionItems === null) {
                continue;
            }
            $collectionItemClassName = $this->getClassName($collectionItems);
            if (!$collectionItemClassName) {
                continue;
            }

            $rowsData = $this->getCollectionItemsRowsData(
                $collectionItemClassName,
                $collectionItems,
                $attribute,
                $parentEntity,
                $collection
            );
            $this->writeRows($worksheet, $rowsData);

            $this->resolveNestedCollectionsRecursively($collectionItemClassName, $spreadsheet, $collectionItems);
        }
    }

    private function getCollectionItemsRowsData(
        string $collectionItemClassName,
        $collectionItems,
        SpreadsheetCollection $attribute,
        $parentEntity,
        \ReflectionProperty $collectionProperty
    ): array {
        $rowsData = [];

        foreach ($collectionItems as $collectionItem) {
            $rowsData[] = $this->getSpreadsheetProperties(
                $collectionItemClassName,
                $collectionItem,
                $attribute->getMappedBy(),
                $parentEntity,
                $collectionProperty
            );
        }

        return $rowsData;
    }

    private function resolveNestedCollectionsRecursively(
        string $collectionItemClassName,
        Spreadsheet $spreadsheet,
        $collectionItems
    ): void {
        $nestedCollections = $this->getPropertiesWithAttribute(
            $collectionItemClassName,
            SpreadsheetCollection::class
        );

        if ($nestedCollections) {
            $this->resolve($spreadsheet, $collectionItems);
        }
    }

    private function humanizeClassName(string $className): string
    {
        return trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $className) ?? $className);
    }
}
