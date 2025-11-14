<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntityWithInvalidInlineCollection::class, 'Test Invalid Inline Sheet')]
class TestEntityWithInvalidInlineCollection
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $name = '';
    
    public array $items = [];
}
