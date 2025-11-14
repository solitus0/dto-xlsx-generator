<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

final class TestReportLineDto
{
    public int $id = 0;

    public int $reportId = 0;

    #[SpreadsheetProperty]
    public string $category = '';

    #[SpreadsheetProperty]
    public float $amount = 0.0;

    #[SpreadsheetCollection(TestReportLineCommentDto::class, mappedBy: 'id', sheetName: 'Report Line Comments')]
    public array $comments = [];
}
