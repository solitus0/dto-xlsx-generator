<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;

class TestNestedCollectionItem
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $value = '';
    
    public int $parentId = 0;
    
    #[SpreadsheetCollection(TestDeepNestedItem::class, mappedBy: 'id', sheetName: 'Deep Nested Items')]
    public ?array $nestedItems = [];
}
