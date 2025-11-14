<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetVirtualProperty;

#[SpreadsheetRoot(TestEntityWithVirtualProperty::class, 'Test Virtual Sheet')]
class TestEntityWithVirtualProperty
{
    public int $id = 0;
    
    #[SpreadsheetProperty]
    public string $firstName = '';
    
    #[SpreadsheetVirtualProperty('Full Name')]
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
    
    public string $lastName = '';
}
