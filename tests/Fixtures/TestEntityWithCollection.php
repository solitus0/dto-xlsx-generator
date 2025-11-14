<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntityWithCollection::class, 'Test Entity With Collection')]
class TestEntityWithCollection
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $name = '';
    
    #[SpreadsheetCollection(TestCollectionItem::class, mappedBy: 'id', sheetName: 'Collection Items')]
    public ?array $items = [];
}
