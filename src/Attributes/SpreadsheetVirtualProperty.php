<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Attributes;

use function call_user_func;
use function method_exists;

#[\Attribute(\Attribute::TARGET_METHOD)]
class SpreadsheetVirtualProperty implements SpreadsheetAttributeInterface
{
    public function __construct(private readonly string $columnName)
    {
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @param callable-string $methodName
     */
    public function getCellValue(string $methodName, mixed $object): mixed
    {
        if (is_object($object) && method_exists($object, $methodName)) {
            /** @var callable():mixed $callback */
            $callback = [$object, $methodName];

            return call_user_func($callback);
        }

        return null;
    }
}
