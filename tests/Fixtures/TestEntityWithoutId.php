<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;

#[SpreadsheetRoot(TestEntityWithoutId::class, 'Test Entity Without Id')]
class TestEntityWithoutId
{
    #[SpreadsheetProperty]
    public string $name = '';
}
