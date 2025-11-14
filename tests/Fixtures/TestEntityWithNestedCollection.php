<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntityWithNestedCollection::class, 'Test Entity With Nested')]
class TestEntityWithNestedCollection
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $name = '';
    
    #[SpreadsheetCollection(TestNestedCollectionItem::class, mappedBy: 'id', sheetName: 'Nested Items')]
    public ?array $items = [];
}
