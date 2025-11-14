<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Unit\AttributesResolver;

use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;
use Solitus0\DtoXlsxGenerator\AttributesResolver\SpreadsheetRootResolver;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntity;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithoutRoot;
use Solitus0\DtoXlsxGenerator\Util\InlineValueSerializer;

class SpreadsheetRootResolverTest extends TestCase
{
    private SpreadsheetRootResolver $resolver;

    private InlineValueSerializer $inlineValueSerializer;

    protected function setUp(): void
    {
        $this->inlineValueSerializer = new InlineValueSerializer();
        $this->resolver = new SpreadsheetRootResolver($this->inlineValueSerializer);
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(99, SpreadsheetRootResolver::getPriority());
    }

    public function testResolveWithValidEntity(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity1 = new TestEntity();
        $entity1->id = 1;
        $entity1->name = 'Test Name 1';
        $entity1->description = 'Test Description 1';
        $entity1->createdAt = new DateTime('2023-01-01 10:00:00');

        $entity2 = new TestEntity();
        $entity2->id = 2;
        $entity2->name = 'Test Name 2';
        $entity2->description = 'Test Description 2';
        $entity2->createdAt = new DateTime('2023-01-02 11:00:00');

        $objects = [$entity1, $entity2];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Test Entity Sheet');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Name', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('Description', $worksheet->getCell('B1')->getValue());
        $this->assertEquals('Created at', $worksheet->getCell('C1')->getValue());

        $this->assertEquals('Test Name 1', $worksheet->getCell('A2')->getValue());
        $this->assertEquals('Test Description 1', $worksheet->getCell('B2')->getValue());
        $this->assertEquals('2023-01-01 10:00:00', $worksheet->getCell('C2')->getValue());

        $this->assertEquals('Test Name 2', $worksheet->getCell('A3')->getValue());
        $this->assertEquals('Test Description 2', $worksheet->getCell('B3')->getValue());
        $this->assertEquals('2023-01-02 11:00:00', $worksheet->getCell('C3')->getValue());
    }

    public function testResolveWithEmptyCollection(): void
    {
        $spreadsheet = new Spreadsheet();
        $objects = [];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNull($spreadsheet->getSheetByName('Test Entity Sheet'));
    }

    public function testResolveWithEntityWithoutRootAttribute(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntityWithoutRoot();
        $entity->id = 1;
        $entity->name = 'Test Name';

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNull($spreadsheet->getSheetByName('Test Entity Sheet'));
    }

    public function testResolveWithSingleEntity(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = 'Single Test';
        $entity->description = 'Single Description';
        $entity->createdAt = new DateTime('2023-01-01 12:00:00');

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Test Entity Sheet');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Single Test', $worksheet->getCell('A2')->getValue());
        $this->assertEquals('Single Description', $worksheet->getCell('B2')->getValue());
        $this->assertEquals('2023-01-01 12:00:00', $worksheet->getCell('C2')->getValue());
    }

    public function testResolveWithEntityHavingEmptyValues(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = '';
        $entity->description = null;
        $entity->createdAt = new DateTime();

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Test Entity Sheet');
        $this->assertNotNull($worksheet);
        $expected = $entity->createdAt->format('Y-m-d H:i:s');
        $this->assertEquals($expected, $worksheet->getCell('A2')->getValue());
        $this->assertEmpty($worksheet->getCell('B2')->getValue());
    }
}
