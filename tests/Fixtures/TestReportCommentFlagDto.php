<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

final class TestReportCommentFlagDto
{
    public int $id = 0;

    public int $commentId = 0;

    #[SpreadsheetProperty]
    public string $type = '';

    #[SpreadsheetProperty]
    public string $note = '';
}
