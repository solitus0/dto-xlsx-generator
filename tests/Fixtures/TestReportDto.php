<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use DateTimeImmutable;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetInlineCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetVirtualProperty;

#[SpreadsheetRoot(TestReportDto::class, 'Reports')]
final class TestReportDto
{
    public int $id = 0;

    #[SpreadsheetProperty]
    public string $title = '';

    #[SpreadsheetProperty]
    public string $owner = '';

    #[SpreadsheetProperty]
    public DateTimeImmutable $generatedAt;

    #[SpreadsheetProperty('formatWithCurrency')]
    public CurrencyAmount $total;

    #[SpreadsheetProperty]
    public array $regions = [];

    #[SpreadsheetInlineCollection(TestReportTag::class)]
    public array $tags = [];

    #[SpreadsheetCollection(TestReportLineDto::class, mappedBy: 'id', sheetName: 'Report Line Items')]
    public array $lines = [];

    public bool $published = false;

    #[SpreadsheetVirtualProperty('lifecycleStatus')]
    public function lifecycleStatus(): string
    {
        return $this->published ? 'Published' : 'Draft';
    }
}
