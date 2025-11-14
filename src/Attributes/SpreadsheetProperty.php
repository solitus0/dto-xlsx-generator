<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SpreadsheetProperty implements SpreadsheetAttributeInterface
{
    public function __construct(
        private readonly ?string $cellValueGetter = null,
    ) {
    }

    public function getCellValueGetter(): ?string
    {
        return $this->cellValueGetter;
    }

    public function getCellValue(mixed $propertyValue): mixed
    {
        if (!$propertyValue) {
            return $propertyValue;
        }

        if ($this->cellValueGetter && is_object($propertyValue)) {
            return (new \ReflectionMethod($propertyValue, $this->cellValueGetter))->invoke($propertyValue);
        }

        return $propertyValue;
    }
}
