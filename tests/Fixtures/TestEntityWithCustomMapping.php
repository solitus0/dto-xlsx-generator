<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntityWithCustomMapping::class, 'Custom Mapping Parent')]
class TestEntityWithCustomMapping
{
    public int $id = 0;

    #[SpreadsheetProperty]
    public string $name = '';

    public ?string $externalCode = '';

    #[SpreadsheetCollection(TestCollectionItem::class, mappedBy: 'externalCode', sheetName: 'Custom Child Items')]
    public array $items = [];
}
