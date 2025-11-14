<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Validator;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetAttributeInterface;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Util\AttributeUtil;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;

final class DtoMetadataCollector
{
    public function __construct(
        private readonly PropertyListExtractorInterface $propertyListExtractor = new ReflectionExtractor(
            accessFlags: ReflectionExtractor::ALLOW_PUBLIC
                | ReflectionExtractor::ALLOW_PROTECTED
                | ReflectionExtractor::ALLOW_PRIVATE
        ),
    ) {
    }

    /**
     * @return array{
     *     hasRoot: bool,
     *     properties: list<array{reflection: ReflectionProperty, attributes: list<SpreadsheetAttributeInterface>}>,
     *     methods: array<string, list<SpreadsheetAttributeInterface>>
     * }
     */
    public function collect(string $className): array
    {
        $reflectionClass = new ReflectionClass($className);

        $propertiesMetadata = [];
        foreach ($this->getProperties($className) as $property) {
            $attributes = $this->filterSpreadsheetAttributes($property->getAttributes());
            if ($attributes === []) {
                continue;
            }

            $propertiesMetadata[] = [
                'reflection' => $property,
                'attributes' => $attributes,
            ];
        }

        $methodsMetadata = [];
        foreach ($reflectionClass->getMethods() as $method) {
            $attributes = $this->filterSpreadsheetAttributes($method->getAttributes());
            if ($attributes === []) {
                continue;
            }

            $methodsMetadata[$method->getName()] = $attributes;
        }

        return [
            'hasRoot' => $this->hasSpreadsheetRootAttribute($className),
            'properties' => $propertiesMetadata,
            'methods' => $methodsMetadata,
        ];
    }

    /**
     * @param array{
     *     hasRoot: bool,
     *     properties: list<array{reflection: ReflectionProperty, attributes: list<SpreadsheetAttributeInterface>}>,
     *     methods: array<string, list<SpreadsheetAttributeInterface>>
     * } $metadata
     */
    public function hasNonRootAttributes(array $metadata): bool
    {
        return $metadata['properties'] !== [] || $metadata['methods'] !== [];
    }

    /**
     * @return ReflectionProperty[]
     */
    private function getProperties(string $className): array
    {
        $properties = [];
        $propertyNames = $this->propertyListExtractor->getProperties($className) ?? [];

        foreach ($propertyNames as $propertyName) {
            if (!property_exists($className, $propertyName)) {
                continue;
            }

            $properties[] = new ReflectionProperty($className, $propertyName);
        }

        return $properties;
    }

    /**
     * @param ReflectionAttribute[] $attributes
     *
     * @return list<SpreadsheetAttributeInterface>
     */
    private function filterSpreadsheetAttributes(array $attributes): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            if (!$instance instanceof SpreadsheetAttributeInterface) {
                continue;
            }

            if ($instance instanceof SpreadsheetRoot) {
                continue;
            }

            $result[] = $instance;
        }

        return $result;
    }

    private function hasSpreadsheetRootAttribute(string $className): bool
    {
        return AttributeUtil::getClassAttribute($className, SpreadsheetRoot::class) instanceof SpreadsheetRoot;
    }
}
