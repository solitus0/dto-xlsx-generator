<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Unit\AttributesResolver;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;
use Solitus0\DtoXlsxGenerator\AttributesResolver\SpreadsheetCollectionResolver;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestCollectionItem;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestDeepNestedItem;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithCollection;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithCustomMapping;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithNestedCollection;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithoutRoot;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestNestedCollectionItem;
use Solitus0\DtoXlsxGenerator\Util\InlineValueSerializer;

class SpreadsheetCollectionResolverTest extends TestCase
{
    private SpreadsheetCollectionResolver $resolver;

    private InlineValueSerializer $inlineValueSerializer;

    protected function setUp(): void
    {
        $this->inlineValueSerializer = new InlineValueSerializer();
        $this->resolver = new SpreadsheetCollectionResolver($this->inlineValueSerializer);
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(98, SpreadsheetCollectionResolver::getPriority());
    }

    public function testResolveWithValidCollection(): void
    {
        $spreadsheet = new Spreadsheet();
        
        $entity = new TestEntityWithCollection();
        $entity->id = 1;
        $entity->name = 'Parent Entity';
        
        $item1 = new TestCollectionItem();
        $item1->id = 1;
        $item1->value = 'Item 1';
        $item1->parentId = 1;
        
        $item2 = new TestCollectionItem();
        $item2->id = 2;
        $item2->value = 'Item 2';
        $item2->parentId = 1;
        
        $entity->items = [$item1, $item2];
        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Collection Items');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Test Entity With Collection Id', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('Id', $worksheet->getCell('B1')->getValue());
        $this->assertEquals('Value', $worksheet->getCell('C1')->getValue());

        $this->assertEquals(1, $worksheet->getCell('A2')->getValue());
        $this->assertEquals(1, $worksheet->getCell('B2')->getValue());
        $this->assertEquals('Item 1', $worksheet->getCell('C2')->getValue());

        $this->assertEquals(1, $worksheet->getCell('A3')->getValue());
        $this->assertEquals(2, $worksheet->getCell('B3')->getValue());
        $this->assertEquals('Item 2', $worksheet->getCell('C3')->getValue());
    }

    public function testResolveWithEmptyCollection(): void
    {
        $spreadsheet = new Spreadsheet();

        $entity = new TestEntityWithCollection();
        $entity->id = 1;
        $entity->name = 'Parent Entity';
        $entity->items = [];

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Collection Items');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Test Entity With Collection Id', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('Id', $worksheet->getCell('B1')->getValue());
        $this->assertEquals('Value', $worksheet->getCell('C1')->getValue());
    }

    public function testResolveWithNullCollection(): void
    {
        $spreadsheet = new Spreadsheet();

        $entity = new TestEntityWithCollection();
        $entity->id = 1;
        $entity->name = 'Parent Entity';
        $entity->items = null;

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Collection Items');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Test Entity With Collection Id', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('Id', $worksheet->getCell('B1')->getValue());
        $this->assertEquals('Value', $worksheet->getCell('C1')->getValue());
    }

    public function testResolveWithEntityWithoutCollections(): void
    {
        $spreadsheet = new Spreadsheet();

        $entity = new TestEntityWithoutRoot();
        $entity->id = 1;
        $entity->name = 'Test Entity';

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNull($spreadsheet->getSheetByName('Collection Items'));
    }

    public function testResolveWithEmptyObjects(): void
    {
        $spreadsheet = new Spreadsheet();
        $objects = [];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNull($spreadsheet->getSheetByName('Collection Items'));
    }

    public function testResolveWithNestedCollections(): void
    {
        $spreadsheet = new Spreadsheet();
        
        $entity = new TestEntityWithNestedCollection();
        $entity->id = 1;
        $entity->name = 'Parent Entity';
        
        $nestedItem1 = new TestNestedCollectionItem();
        $nestedItem1->id = 1;
        $nestedItem1->value = 'Nested Item 1';
        $nestedItem1->parentId = 1;
        
        $deepItem1 = new TestDeepNestedItem();
        $deepItem1->id = 1;
        $deepItem1->description = 'Deep Item 1';
        $deepItem1->nestedParentId = 1;
        
        $deepItem2 = new TestDeepNestedItem();
        $deepItem2->id = 2;
        $deepItem2->description = 'Deep Item 2';
        $deepItem2->nestedParentId = 1;
        
        $nestedItem1->nestedItems = [$deepItem1, $deepItem2];
        $entity->items = [$nestedItem1];
        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $nestedWorksheet = $spreadsheet->getSheetByName('Nested Items');
        $this->assertNotNull($nestedWorksheet);
        $this->assertEquals('Test Entity With Nested Collection Id', $nestedWorksheet->getCell('A1')->getValue());
        $this->assertEquals('Id', $nestedWorksheet->getCell('B1')->getValue());
        $this->assertEquals('Value', $nestedWorksheet->getCell('C1')->getValue());

        $deepWorksheet = $spreadsheet->getSheetByName('Deep Nested Items');
        $this->assertNotNull($deepWorksheet);
        $this->assertEquals('Test Nested Collection Item Id', $deepWorksheet->getCell('A1')->getValue());
        $this->assertEquals('Id', $deepWorksheet->getCell('B1')->getValue());
        $this->assertEquals('Description', $deepWorksheet->getCell('C1')->getValue());

        $this->assertEquals(1, $deepWorksheet->getCell('A2')->getValue());
        $this->assertEquals(1, $deepWorksheet->getCell('B2')->getValue());
        $this->assertEquals('Deep Item 1', $deepWorksheet->getCell('C2')->getValue());

        $this->assertEquals(1, $deepWorksheet->getCell('A3')->getValue());
        $this->assertEquals(2, $deepWorksheet->getCell('B3')->getValue());
        $this->assertEquals('Deep Item 2', $deepWorksheet->getCell('C3')->getValue());
    }

    public function testResolveWithMultipleParentEntities(): void
    {
        $spreadsheet = new Spreadsheet();
        
        $entity1 = new TestEntityWithCollection();
        $entity1->id = 1;
        $entity1->name = 'Parent Entity 1';
        
        $item1 = new TestCollectionItem();
        $item1->id = 1;
        $item1->value = 'Item 1';
        $item1->parentId = 1;
        
        $entity1->items = [$item1];
        
        $entity2 = new TestEntityWithCollection();
        $entity2->id = 2;
        $entity2->name = 'Parent Entity 2';
        
        $item2 = new TestCollectionItem();
        $item2->id = 2;
        $item2->value = 'Item 2';
        $item2->parentId = 2;
        
        $entity2->items = [$item2];
        $objects = [$entity1, $entity2];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Collection Items');
        $this->assertNotNull($worksheet);
        
        $this->assertEquals(1, $worksheet->getCell('A2')->getValue());
        $this->assertEquals(1, $worksheet->getCell('B2')->getValue());
        $this->assertEquals('Item 1', $worksheet->getCell('C2')->getValue());

        $this->assertEquals(2, $worksheet->getCell('A3')->getValue());
        $this->assertEquals(2, $worksheet->getCell('B3')->getValue());
        $this->assertEquals('Item 2', $worksheet->getCell('C3')->getValue());
    }

    public function testResolveUsesCustomParentPropertyForMapping(): void
    {
        $spreadsheet = new Spreadsheet();

        $parent = new TestEntityWithCustomMapping();
        $parent->id = 10;
        $parent->name = 'Custom Parent';
        $parent->externalCode = 'EXT-99';

        $child = new TestCollectionItem();
        $child->id = 5;
        $child->value = 'Custom Child';

        $parent->items = [$child];

        $this->resolver->resolve($spreadsheet, [$parent]);

        $worksheet = $spreadsheet->getSheetByName('Custom Child Items');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Test Entity With Custom Mapping External code', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('EXT-99', $worksheet->getCell('A2')->getValue());
        $this->assertEquals('Id', $worksheet->getCell('B1')->getValue());
        $this->assertEquals(5, $worksheet->getCell('B2')->getValue());
    }
}
