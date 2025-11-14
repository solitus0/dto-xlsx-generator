<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetInlineCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntityWithInlineCollection::class, 'Test Inline Sheet')]
class TestEntityWithInlineCollection
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $name = '';
    
    #[SpreadsheetInlineCollection(TestInlineItem::class)]
    public ?array $items = [];
}
