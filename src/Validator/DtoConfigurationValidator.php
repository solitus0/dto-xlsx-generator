<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Validator;

use ReflectionProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetInlineCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

final class DtoConfigurationValidator
{
    public function __construct(private readonly DtoMetadataCollector $metadataCollector = new DtoMetadataCollector())
    {
    }

    /**
     * @return string[]
     */
    public function validate(string $dtoClass): array
    {
        if (!class_exists($dtoClass)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $dtoClass));
        }

        $visited = [];

        return $this->collectErrors($dtoClass, true, $visited);
    }

    /**
     * @param array<string,bool> $visited
     *
     * @return string[]
     */
    private function collectErrors(string $className, bool $requireRootAttribute, array &$visited): array
    {
        if (isset($visited[$className])) {
            return [];
        }

        $visited[$className] = true;
        $errors = [];

        $metadata = $this->metadataCollector->collect($className);

        if ($requireRootAttribute && !$metadata['hasRoot']) {
            $errors[] = sprintf('DTO "%s" must declare the #[SpreadsheetRoot] attribute.', $className);
        }

        if (!$this->metadataCollector->hasNonRootAttributes($metadata)) {
            $errors[] = sprintf(
                'DTO "%s" must declare at least one spreadsheet-mapped property or method.',
                $className
            );
        }

        $sheetNames = [];

        foreach ($metadata['properties'] as $propertyMeta) {
            /** @var ReflectionProperty $property */
            $property = $propertyMeta['reflection'];

            foreach ($propertyMeta['attributes'] as $attribute) {
                if ($attribute instanceof SpreadsheetProperty) {
                    $this->validateCellValueGetter(
                        owningClass: $className,
                        property: $property,
                        attribute: $attribute,
                        errors: $errors,
                    );
                }

                if ($attribute instanceof SpreadsheetCollection) {
                    $sheetName = $attribute->getSheetName();
                    if (isset($sheetNames[$sheetName])) {
                        $errors[] = sprintf(
                            'DTO "%s" uses #[SpreadsheetCollection] on "%s" but sheet name "%s" is already used by "%s".',
                            $className,
                            $property->getName(),
                            $sheetName,
                            $sheetNames[$sheetName]
                        );
                    } else {
                        $sheetNames[$sheetName] = $property->getName();
                    }

                    $targetClass = $attribute->getClassName();

                    $this->validateTargetDto(
                        owningClass: $className,
                        property: $property,
                        targetClass: $targetClass,
                        attributeLabel: 'SpreadsheetCollection',
                        errors: $errors,
                        visited: $visited,
                    );
                }

                if ($attribute instanceof SpreadsheetInlineCollection) {
                    $this->validateTargetDto(
                        owningClass: $className,
                        property: $property,
                        targetClass: $attribute->getClassName(),
                        attributeLabel: 'SpreadsheetInlineCollection',
                        errors: $errors,
                        visited: $visited,
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Shared logic for validating that a target DTO class exists, has attributes,
     * and passes nested validation.
     *
     * @param array<string,bool> $visited
     * @param string[] $errors
     */
    private function validateTargetDto(
        string $owningClass,
        ReflectionProperty $property,
        string $targetClass,
        string $attributeLabel,
        array &$errors,
        array &$visited,
    ): void {
        if (!class_exists($targetClass)) {
            $errors[] = sprintf(
                'DTO "%s" uses #[%s] on "%s" but target class "%s" does not exist.',
                $owningClass,
                $attributeLabel,
                $property->getName(),
                $targetClass
            );

            return;
        }

        $targetMeta = $this->metadataCollector->collect($targetClass);

        if (!$this->metadataCollector->hasNonRootAttributes($targetMeta)) {
            $errors[] = sprintf(
                'DTO "%s" uses #[%s] on "%s" but target class "%s" has no spreadsheet attributes.',
                $owningClass,
                $attributeLabel,
                $property->getName(),
                $targetClass
            );

            return;
        }

        $errors = array_merge(
            $errors,
            $this->collectErrors($targetClass, false, $visited)
        );
    }

    /**
     * @param string[] $errors
     */
    private function validateCellValueGetter(
        string $owningClass,
        ReflectionProperty $property,
        SpreadsheetProperty $attribute,
        array &$errors,
    ): void {
        $cellValueGetter = $attribute->getCellValueGetter();
        if ($cellValueGetter === null) {
            return;
        }

        $propertyTypes = $this->extractPropertyClassTypes($property);
        if ($propertyTypes === []) {
            return;
        }

        foreach ($propertyTypes as $type) {
            if (!class_exists($type) && !interface_exists($type)) {
                continue;
            }

            if (!method_exists($type, $cellValueGetter)) {
                $errors[] = sprintf(
                    'DTO "%s" uses #[SpreadsheetProperty] on "%s" with cellValueGetter "%s" but method does not exist on type "%s".',
                    $owningClass,
                    $property->getName(),
                    $cellValueGetter,
                    $type,
                );
            }
        }
    }

    /**
     * @return list<class-string>
     */
    private function extractPropertyClassTypes(ReflectionProperty $property): array
    {
        $type = $property->getType();
        if ($type === null) {
            return [];
        }

        $namedTypes = [];
        if ($type instanceof \ReflectionNamedType) {
            $namedTypes[] = $type;
        }

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            foreach ($type->getTypes() as $childType) {
                if ($childType instanceof \ReflectionNamedType) {
                    $namedTypes[] = $childType;
                }
            }
        }

        $classNames = [];
        foreach ($namedTypes as $namedType) {
            if ($namedType->isBuiltin()) {
                continue;
            }

            $typeName = $namedType->getName();
            if ($typeName === 'self' || $typeName === 'static') {
                $typeName = $property->getDeclaringClass()->getName();
            } elseif ($typeName === 'parent') {
                $parent = $property->getDeclaringClass()->getParentClass();
                if ($parent === false) {
                    continue;
                }

                $typeName = $parent->getName();
            }

            $classNames[] = $typeName;
        }

        return array_values(array_unique($classNames));
    }
}
