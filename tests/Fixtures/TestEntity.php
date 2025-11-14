<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use DateTime;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntity::class, 'Test Entity Sheet')]
class TestEntity
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $name = '';
    
    #[SpreadsheetProperty]
    public ?string $description = null;
    
    #[SpreadsheetProperty]
    public DateTime $createdAt;
}
