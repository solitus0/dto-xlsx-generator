<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

class TestInlineItem
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $name = '';
    
    #[SpreadsheetProperty]
    public string $value = '';
}
