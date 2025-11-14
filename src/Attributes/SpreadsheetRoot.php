<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SpreadsheetRoot implements SpreadsheetAttributeInterface, WorksheetInterface
{
    private ?int $depth = 0;

    private ?int $sheetIndex = null;

    public function __construct(
        private readonly string $className,
        private readonly ?string $sheetName = null,
    ) {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getSheetName(): string
    {
        return $this->sheetName ?? $this->humanizeClassName($this->className);
    }

    public function getSheetIndex(): ?int
    {
        return $this->sheetIndex;
    }

    public function setSheetIndex(?int $sheetIndex): self
    {
        $this->sheetIndex = $sheetIndex;

        return $this;
    }

    public function getMappedBy(): ?string
    {
        return null;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(?int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    private function humanizeClassName(string $className): string
    {
        $shortName = str_contains($className, '\\') ? substr($className, strrpos($className, '\\') + 1) : $className;

        return trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $shortName) ?? $shortName);
    }
}
