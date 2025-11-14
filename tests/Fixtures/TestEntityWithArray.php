<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntityWithArray::class, 'Test Array Sheet')]
class TestEntityWithArray
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public array $tags = [];
}
