<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SpreadsheetInlineCollection implements SpreadsheetAttributeInterface
{
    public function __construct(
        private readonly string $className,
    ) {
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
