<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Assertions;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\TestCase;

final class WorksheetAssert
{
    /**
     * @param array<string, mixed> $expectedCells keyed by column letter.
     */
    public static function assertRowEquals(
        TestCase $testCase,
        Worksheet $sheet,
        int $rowIndex,
        array $expectedCells,
        string $message = ''
    ): void {
        foreach ($expectedCells as $column => $expectedValue) {
            $coordinate = sprintf('%s%d', $column, $rowIndex);
            $actualValue = $sheet->getCell($coordinate)->getValue();
            $testCase->assertSame(
                $expectedValue,
                $actualValue,
                $message ?: sprintf('Failed asserting %s matches expected value', $coordinate)
            );
        }
    }
}
