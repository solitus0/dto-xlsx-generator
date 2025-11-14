<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\AttributesResolver;

use Doctrine\Common\Util\ClassUtils;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetInlineCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetVirtualProperty;
use Solitus0\DtoXlsxGenerator\Attributes\WorksheetInterface;
use Solitus0\DtoXlsxGenerator\Util\ArrayPropertyUtil;
use Solitus0\DtoXlsxGenerator\Util\AttributeUtil;
use Solitus0\DtoXlsxGenerator\Util\InlineValueSerializer;

abstract class AbstractSpreadsheetResolver implements SpreadsheetResolverInterface
{
    protected array $objectProperties = [];

    protected array $columnIndexes = [];

    protected array $lastSpreadsheetRowIndex = [];

    protected array $worksheetAttributes = [];

    protected array $worksheetDepth = [];

    public function __construct(private readonly InlineValueSerializer $getInlineValueUseCase)
    {
    }

    public function resetInMemoryCache(): void
    {
        $this->objectProperties = [];
        $this->columnIndexes = [];
        $this->lastSpreadsheetRowIndex = [];
        $this->worksheetAttributes = [];
        $this->worksheetDepth = [];
    }

    protected function getSpreadsheetProperties(
        string $className,
        object $object,
        string $mappedBy = null,
        object $parentEntity = null,
        ?\ReflectionProperty $collectionProperty = null,
    ): array {
        $isRoot = $this->isSpreadsheetRoot($object);
        $colIndex = $isRoot ? 0 : 2;

        $result = [];
        $result = $this->resolveProperties($className, $object, $colIndex, $result);
        $result = $this->resolveVirtualProperties($className, $object, $colIndex, $result);
        $result = $this->resolveInlineCollectionProperties($className, $object, $colIndex, $result);

        if ($result && $mappedBy !== null) {
            if ($parentEntity === null) {
                throw new \LogicException(sprintf(
                    'Mapped spreadsheet collection "%s" requires a parent entity instance.',
                    $mappedBy
                ));
            }

            $result['A']['name'] = $mappedBy;
            $result['A']['value'] = $this->getParentMappingValue($parentEntity, $mappedBy, $collectionProperty);
        }

        if ($result && !$isRoot) {
            $result['B']['name'] = 'Id';
            $result['B']['value'] = $this->getObjectId($object);
        }

        return $result;
    }

    private function isSpreadsheetRoot(object|string $class): bool
    {
        if (is_object($class)) {
            $class = ClassUtils::getClass($class);
        }

        if (!$class) {
            return false;
        }

        return (bool)AttributeUtil::getClassAttribute($class, SpreadsheetRoot::class);
    }

    private function resolveProperties(string $className, object $object, int &$colIndex, array &$result): array
    {
        $properties = $this->getPropertiesWithAttribute($className, SpreadsheetProperty::class);
        foreach ($properties as $property) {
            $attribute = AttributeUtil::getPropertyAttribute($property, SpreadsheetProperty::class);
            if (!$attribute instanceof SpreadsheetProperty) {
                continue;
            }

            $column = $this->getAlphabeticColumnIndex($colIndex);

            $propertyValue = $property->getValue($object);
            $propertyValue = $this->getCellValue($attribute, $propertyValue);
            if ($propertyValue === null || $propertyValue === '') {
                continue;
            }

            $colIndex++;
            $propertyName = $property->getName();
            $result[$column]['name'] = $propertyName;
            $result[$column]['value'] = $propertyValue;
        }

        return $result;
    }

    protected function getPropertiesWithAttribute(string $objectClass, string $attributeClass): array
    {
        $cacheKey = sprintf('%s_%s', $objectClass, $attributeClass);
        if (isset($this->objectProperties[$cacheKey])) {
            return $this->objectProperties[$cacheKey];
        }

        $propertiesWithAttribute = AttributeUtil::getPropertiesWithAttribute($objectClass, $attributeClass);

        $this->objectProperties[$cacheKey] = $propertiesWithAttribute;

        return $propertiesWithAttribute;
    }

    protected function getAlphabeticColumnIndex(int $index): string
    {
        if (ArrayPropertyUtil::getProperty($this->columnIndexes, $index)) {
            return $this->columnIndexes[$index];
        }

        $column = '';
        $i = $index;
        for (; $i >= 0; $i = (int)($i / 26) - 1) {
            $column = chr($i % 26 + 0x41) . $column;
        }

        $this->columnIndexes[$index] = $column;

        return $column;
    }

    private function getCellValue(SpreadsheetProperty $attribute, mixed $propertyValue): mixed
    {
        if ($propertyValue instanceof \DateTimeInterface) {
            $propertyValue = $propertyValue->format('Y-m-d H:i:s');
        }
        
        if ($propertyValue instanceof \BackedEnum) {
            $propertyValue = $propertyValue->value;
        }

        $propertyValue = $attribute->getCellValue($propertyValue);
        if (is_string($propertyValue)) {
            $propertyValue = trim($propertyValue);
        }

        if (is_array($propertyValue)) {
            $propertyValue = implode(',', $propertyValue);
        }

        return $propertyValue;
    }

    private function resolveVirtualProperties(string $className, object $object, int &$colIndex, array &$result): array
    {
        $methods = $this->getMethodsWithAttribute($className, SpreadsheetVirtualProperty::class);
        foreach ($methods as $method) {
            $attribute = AttributeUtil::getMethodAttribute($method, SpreadsheetVirtualProperty::class);
            if (!$attribute instanceof SpreadsheetVirtualProperty) {
                continue;
            }

            $column = $this->getAlphabeticColumnIndex($colIndex);
            $methodName = $method->getName();
            $cellValue = $this->getCellFromVirtualPropertyValue($attribute, $methodName, $object);
            if ($cellValue === null || $cellValue === '') {
                continue;
            }

            $colIndex++;
            $result[$column]['name'] = $attribute->getColumnName();
            $result[$column]['value'] = $cellValue;
        }

        return $result;
    }

    protected function getMethodsWithAttribute(string $objectClass, string $attributeClass): array
    {
        $cacheKey = sprintf('%s_%s', $objectClass, $attributeClass);
        if (isset($this->objectProperties[$cacheKey])) {
            return $this->objectProperties[$cacheKey];
        }

        $methodsWithAttribute = AttributeUtil::getMethodsWithAttribute($objectClass, $attributeClass);

        $this->objectProperties[$cacheKey] = $methodsWithAttribute;

        return $methodsWithAttribute;
    }

    private function getCellFromVirtualPropertyValue(
        SpreadsheetVirtualProperty $attribute,
        string $methodName,
        object $rootObject
    ): mixed {
        $cellValue = $attribute->getCellValue($methodName, $rootObject);

        if ($cellValue instanceof \DateTimeInterface) {
            $cellValue = $cellValue->format('Y-m-d H:i:s');
        }

        if (is_string($cellValue)) {
            $cellValue = trim($cellValue);
        }

        if (is_array($cellValue)) {
            $cellValue = implode(',', $cellValue);
        }

        return $cellValue;
    }

    private function resolveInlineCollectionProperties(
        string $className,
        object $object,
        int &$colIndex,
        array &$result
    ): array {
        $inlineCollections = $this->getPropertiesWithAttribute($className, SpreadsheetInlineCollection::class);
        foreach ($inlineCollections as $inlineCollection) {
            $attribute = AttributeUtil::getPropertyAttribute($inlineCollection, SpreadsheetInlineCollection::class);
            if (!$attribute instanceof SpreadsheetInlineCollection) {
                continue;
            }

            $column = $this->getAlphabeticColumnIndex($colIndex);
            $colIndex++;

            $collectionItems = $inlineCollection->getValue($object);
            $inlineValues = [];
            foreach ($collectionItems as $index => $collectionItem) {
                $collectionItemProperties = $this->getPropertiesWithAttribute(
                    $collectionItem::class,
                    SpreadsheetProperty::class
                );

                foreach ($collectionItemProperties as $inlineProperty) {
                    $attribute = AttributeUtil::getPropertyAttribute($inlineProperty, SpreadsheetProperty::class);
                    if (!$attribute instanceof SpreadsheetProperty) {
                        continue;
                    }

                    $propertyName = $inlineProperty->getName();
                    $propertyValue = $inlineProperty->getValue($collectionItem);
                    $propertyValue = $this->getCellValue($attribute, $propertyValue);
                    if ($propertyValue === null || $propertyValue === '') {
                        continue;
                    }

                    $inlineValues[$index][] = sprintf('%s: %s', $propertyName, $propertyValue);
                }
            }

            $inlineValue = $this->getInlineValueUseCase->serialize($inlineValues);
            $propertyName = $inlineCollection->getName();
            $result[$column]['name'] = $propertyName;
            $result[$column]['value'] = $inlineValue;
        }

        return $result;
    }

    private function getParentMappingValue(
        object $parentEntity,
        string $mappedBy,
        ?\ReflectionProperty $collectionProperty
    ): mixed {
        $value = $this->readObjectProperty($parentEntity, $mappedBy, $collectionProperty);
        if ($value === null) {
            $context = $collectionProperty
                ? sprintf(' declared on %s::%s', $collectionProperty->getDeclaringClass()->getName(), $collectionProperty->getName())
                : '';

            throw new \LogicException(sprintf(
                'DTO "%s"%s must provide a non-null value for property "%s" when used with #[SpreadsheetCollection(mappedBy: "%s")].',
                $parentEntity::class,
                $context,
                $mappedBy,
                $mappedBy
            ));
        }

        return $value;
    }

    private function readObjectProperty(
        object $object,
        string $propertyName,
        ?\ReflectionProperty $collectionProperty
    ): mixed {
        $reflection = new \ReflectionClass(ClassUtils::getClass($object));
        if (!$reflection->hasProperty($propertyName)) {
            $context = $collectionProperty
                ? sprintf(' declared on %s::%s', $collectionProperty->getDeclaringClass()->getName(), $collectionProperty->getName())
                : '';

            throw new \LogicException(sprintf(
                'DTO "%s"%s does not define property "%s" required by #[SpreadsheetCollection(mappedBy: "%s")].',
                $reflection->getName(),
                $context,
                $propertyName,
                $propertyName
            ));
        }

        $property = $reflection->getProperty($propertyName);
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }

        return $property->getValue($object);
    }

    private function getObjectId(object $object): int|string
    {
        $class = ClassUtils::getClass($object);
        $reflectionClass = new \ReflectionClass($class);

        if ($reflectionClass->hasProperty('id')) {
            $property = $reflectionClass->getProperty('id');
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }

            $value = $property->getValue($object);
            if ($value !== null) {
                return $value;
            }
        }

        if ($reflectionClass->hasMethod('getId')) {
            $method = $reflectionClass->getMethod('getId');
            if ($method->getNumberOfRequiredParameters() === 0) {
                $value = $method->invoke($object);
                if ($value !== null) {
                    return $value;
                }
            }
        }

        throw new \LogicException(sprintf(
            'DTO "%s" must expose an "id" property or getId() when used with #[SpreadsheetCollection].',
            $reflectionClass->getName()
        ));
    }

    protected function getWorksheet(Spreadsheet $spreadsheet, WorksheetInterface $attribute): Worksheet
    {
        $worksheet = $spreadsheet->getSheetByName($attribute->getSheetName());
        if (!$worksheet instanceof Worksheet) {
            $worksheet = $this->createWorksheet($spreadsheet, $attribute);
        }

        return $worksheet;
    }

    protected function createWorksheet(
        Spreadsheet $spreadsheet,
        WorksheetInterface $attribute,
    ): Worksheet {
        $worksheet = $spreadsheet->createSheet($attribute->getSheetIndex());
        $worksheet->setTitle($attribute->getSheetName());

        $properties = $this->getPropertiesWithAttribute(
            $attribute->getClassName(),
            SpreadsheetProperty::class
        );

        $virtualProperties = $this->getVirtualProperties(
            $attribute->getClassName(),
        );

        $inlineCollections = $this->getPropertiesWithAttribute(
            $attribute->getClassName(),
            SpreadsheetInlineCollection::class
        );

        $properties = array_merge($properties, $virtualProperties, $inlineCollections);
        $isRoot = $this->isSpreadsheetRoot($attribute->getClassName());
        $this->addMappingColumn($attribute->getMappedBy(), $worksheet, $isRoot, $attribute);
        $this->addHeader($worksheet, $properties, $isRoot);

        return $worksheet;
    }

    protected function getClassName(iterable $objects): ?string
    {
        $objectClass = null;
        foreach ($objects as $object) {
            $objectClass = ClassUtils::getClass($object);
            break;
        }

        return $objectClass;
    }

    protected function getVirtualProperties(string $objectClass)
    {
        $cacheKey = sprintf('%s_%s_properties', $objectClass, SpreadsheetVirtualProperty::class);
        if (isset($this->objectProperties[$cacheKey])) {
            return $this->objectProperties[$cacheKey];
        }

        $propertiesWithAttribute = AttributeUtil::getVirtualPropertiesWithMethodAttribute($objectClass);

        $this->objectProperties[$cacheKey] = $propertiesWithAttribute;

        return $propertiesWithAttribute;
    }

    private function addMappingColumn(
        ?string $mappedBy,
        Worksheet $worksheet,
        bool $isRoot,
        ?WorksheetInterface $attribute = null
    ): void {
        if ($isRoot && $mappedBy === null) {
            return;
        }

        $prefix = null;
        if ($attribute instanceof SpreadsheetCollection) {
            $prefix = $attribute->getMappedByHeaderPrefix();
        }

        $mappedByLabel = $mappedBy ? $this->camelCaseToSentenceCase($mappedBy) : 'Id';
        $label = trim(sprintf('%s %s', $prefix ?? '', $mappedByLabel));
        $worksheet->setCellValue('A1', $label === '' ? 'Id' : $label);
        $worksheet->getColumnDimension('A')->setAutoSize(true);
        $worksheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if (!$isRoot) {
            $worksheet->setCellValue('B1', 'Id');
            $worksheet->getColumnDimension('B')->setAutoSize(true);
            $worksheet->getStyle('B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }

    private function camelCaseToSentenceCase($name): string
    {
        return ucfirst(
            preg_replace_callback('/[A-Z]/', static function ($matches) {
                return ' ' . strtolower($matches[0]);
            }, $name)
        );
    }

    protected function addHeader(Worksheet $worksheet, array $properties, bool $isRoot): void
    {
        $colStart = $isRoot ? 0 : 2;

        foreach ($properties as $index => $property) {
            $name = $this->camelCaseToSentenceCase($property->getName());
            $colIndex = $colStart + $index;
            $column = $this->getAlphabeticColumnIndex($colIndex);
            $worksheet->setCellValue($column . 1, $name);
            $worksheet->getColumnDimension($column)->setAutoSize(true);
            $worksheet->getStyle($column)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
    }

    protected function writeRows(Worksheet $sheet, array $rowsData): void
    {
        if (!$rowsData) {
            return;
        }

        foreach ($rowsData as $rowData) {
            $this->writeRow($sheet, $rowData);
        }
    }

    private function writeRow(Worksheet $sheet, array $rowData): void
    {
        $name = $sheet->getTitle();
        $lastRowIndex = ArrayPropertyUtil::getProperty($this->lastSpreadsheetRowIndex, $name, 2);
        foreach ($rowData as $column => $value) {
            $coordinate = $column . $lastRowIndex;
            $cellValue = $value['value'];
            if (is_string($cellValue)) {
                $sheet->setCellValueExplicit($coordinate, $cellValue, DataType::TYPE_STRING2);
                continue;
            }

            $sheet->setCellValue($coordinate, $cellValue);
        }

        $rowIndex = $lastRowIndex + 1;
        $this->lastSpreadsheetRowIndex[$name] = $rowIndex;
    }
}
