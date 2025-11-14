<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

final class TestReportLineCommentDto
{
    public int $id = 0;

    public int $lineId = 0;

    #[SpreadsheetProperty]
    public string $author = '';

    #[SpreadsheetProperty]
    public string $message = '';

    #[SpreadsheetCollection(TestReportCommentFlagDto::class, mappedBy: 'id', sheetName: 'Report Comment Flags')]
    public array $flags = [];
}
