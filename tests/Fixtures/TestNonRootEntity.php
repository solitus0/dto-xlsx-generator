<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

class TestNonRootEntity
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $value = '';
}
