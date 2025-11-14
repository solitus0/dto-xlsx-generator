<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestCollectionParentWithoutId::class, 'Parent Without Id')]
class TestCollectionParentWithoutId
{
    #[SpreadsheetProperty]
    public string $name = '';

    #[SpreadsheetCollection(TestCollectionItem::class, mappedBy: 'parentId', sheetName: 'Items')]
    public array $items = [];
}
