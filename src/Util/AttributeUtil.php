<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Util;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetAttributeInterface;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetVirtualProperty;

class AttributeUtil
{
    public static function getPropertyAttribute(
        \ReflectionProperty $property,
        string $attributeClass
    ): ?SpreadsheetAttributeInterface {
        $attributes = $property->getAttributes($attributeClass);
        if ($attributes) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    public static function getMethodAttribute(
        \ReflectionMethod $function,
        string $attributeClass
    ): ?SpreadsheetAttributeInterface {
        $attributes = $function->getAttributes($attributeClass);
        if ($attributes) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    public static function getVirtualPropertiesWithMethodAttribute(string $objectClass): array
    {
        $class = new \ReflectionClass($objectClass);
        $methods = $class->getMethods();

        $methodsWithAttribute = [];
        foreach ($methods as $method) {
            $attribute = self::getMethodAttribute($method, SpreadsheetVirtualProperty::class);
            if ($attribute instanceof SpreadsheetVirtualProperty) {
                $methodsWithAttribute[] = new class($attribute) {
                    public function __construct(private readonly SpreadsheetVirtualProperty $attribute)
                    {
                    }

                    public function getName(): string
                    {
                        return $this->attribute->getColumnName();
                    }
                };
            }
        }

        return $methodsWithAttribute;
    }

    public static function getMethodsWithAttribute(string $objectClass, string $attributeClass): array
    {
        $class = new \ReflectionClass($objectClass);
        $methods = $class->getMethods();

        $methodsWithAttribute = [];
        foreach ($methods as $method) {
            $attribute = self::getMethodAttribute($method, $attributeClass);
            if ($attribute instanceof $attributeClass) {
                $methodsWithAttribute[] = $method;
            }
        }

        return $methodsWithAttribute;
    }

    public static function getPropertiesWithAttribute(string $objectClass, string $attributeClass): array
    {
        $class = new \ReflectionClass($objectClass);
        $properties = $class->getProperties();

        $propertiesWithAttribute = [];
        foreach ($properties as $property) {
            $attribute = self::getPropertyAttribute($property, $attributeClass);
            if ($attribute instanceof $attributeClass) {
                $propertiesWithAttribute[] = $property;
            }
        }

        return $propertiesWithAttribute;
    }

    public static function getClassAttribute(
        ?string $className,
        string $attributeClass
    ): ?SpreadsheetAttributeInterface {
        if (!$className) {
            return null;
        }

        $class = new \ReflectionClass($className);
        $attributes = $class->getAttributes($attributeClass);
        if (count($attributes) > 0) {
            return $attributes[0]->newInstance();
        }

        return null;
    }
}
