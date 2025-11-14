<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

final class TestReportTag
{
    #[SpreadsheetProperty]
    public string $label;

    #[SpreadsheetProperty]
    public string $color;

    public function __construct(string $label, string $color)
    {
        $this->label = $label;
        $this->color = $color;
    }
}
