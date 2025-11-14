<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

class TestCollectionItem
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $value = '';
    
    public int $parentId = 0;
}
