<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Unit\AttributesResolver;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;
use Solitus0\DtoXlsxGenerator\AttributesResolver\WorksheetsResolver;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestDeepNestedItem;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntity;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithCollection;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithNestedCollection;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithoutRoot;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestNestedCollectionItem;
use Solitus0\DtoXlsxGenerator\Util\InlineValueSerializer;

class WorksheetsResolverTest extends TestCase
{
    private WorksheetsResolver $resolver;

    private InlineValueSerializer $inlineValueSerializer;

    protected function setUp(): void
    {
        $this->inlineValueSerializer = new InlineValueSerializer();
        $this->resolver = new WorksheetsResolver($this->inlineValueSerializer);
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(100, WorksheetsResolver::getPriority());
    }

    public function testResolveWithEntityWithoutRootAttribute(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntityWithoutRoot();
        $entity->id = 1;
        $entity->name = 'Test Entity';

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNull($spreadsheet->getSheetByName('Test Entity Sheet'));
    }

    public function testResolveWithEmptyCollection(): void
    {
        $spreadsheet = new Spreadsheet();
        $objects = [];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNull($spreadsheet->getSheetByName('Test Entity Sheet'));
    }

    public function testResolveWithSimpleEntity(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = 'Test Name';

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Test Entity Sheet');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Test Entity Sheet', $worksheet->getTitle());
    }

    public function testResolveWithEntityWithCollection(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntityWithCollection();
        $entity->id = 1;
        $entity->name = 'Parent Entity';
        $entity->items = [];

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $rootWorksheet = $spreadsheet->getSheetByName('Test Entity With Collection');
        $this->assertNotNull($rootWorksheet);
        $this->assertEquals('Test Entity With Collection', $rootWorksheet->getTitle());

        $collectionWorksheet = $spreadsheet->getSheetByName('Collection Items');
        $this->assertNotNull($collectionWorksheet);
        $this->assertEquals('Collection Items', $collectionWorksheet->getTitle());
    }

    public function testResolveWithEntityWithNestedCollections(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntityWithNestedCollection();
        $entity->id = 1;
        $entity->name = 'Parent Entity';
        $entity->items = [];

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $rootWorksheet = $spreadsheet->getSheetByName('Test Entity With Nested');
        $this->assertNotNull($rootWorksheet);
        $this->assertEquals('Test Entity With Nested', $rootWorksheet->getTitle());

        $nestedWorksheet = $spreadsheet->getSheetByName('Nested Items');
        $this->assertNotNull($nestedWorksheet);
        $this->assertEquals('Nested Items', $nestedWorksheet->getTitle());

        $deepWorksheet = $spreadsheet->getSheetByName('Deep Nested Items');
        $this->assertNotNull($deepWorksheet);
        $this->assertEquals('Deep Nested Items', $deepWorksheet->getTitle());
    }

    public function testResolveWithExistingWorksheet(): void
    {
        $spreadsheet = new Spreadsheet();
        $existingWorksheet = $spreadsheet->createSheet();
        $existingWorksheet->setTitle('Test Entity Sheet');

        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = 'Test Name';

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertCount(2, $spreadsheet->getAllSheets());
        $this->assertSame($existingWorksheet, $spreadsheet->getSheetByName('Test Entity Sheet'));
    }

    public function testResolveWithMultipleEntities(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity1 = new TestEntity();
        $entity1->id = 1;
        $entity1->name = 'Test Name 1';
        
        $entity2 = new TestEntity();
        $entity2->id = 2;
        $entity2->name = 'Test Name 2';
        
        $objects = [$entity1, $entity2];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet = $spreadsheet->getSheetByName('Test Entity Sheet');
        $this->assertNotNull($worksheet);
        $this->assertEquals('Test Entity Sheet', $worksheet->getTitle());
    }

    public function testResolveWithMixedEntityTypes(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity1 = new TestEntity();
        $entity1->id = 1;
        $entity1->name = 'Test Name 1';
        
        $entity2 = new TestEntityWithCollection();
        $entity2->id = 2;
        $entity2->name = 'Test Name 2';
        $entity2->items = [];
        
        $objects = [$entity1, $entity2];

        $this->resolver->resolve($spreadsheet, $objects);

        $worksheet1 = $spreadsheet->getSheetByName('Test Entity Sheet');
        $this->assertNotNull($worksheet1);
        
        $worksheet2 = $spreadsheet->getSheetByName('Test Entity With Collection');
        $this->assertNull($worksheet2);
        
        $worksheet3 = $spreadsheet->getSheetByName('Collection Items');
        $this->assertNull($worksheet3);
    }

    public function testResolveResetsInMemoryCache(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = 'Test Name';

        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $reflection = new \ReflectionClass($this->resolver);
        $worksheetAttributes = $reflection->getProperty('worksheetAttributes');

        $this->assertNotEmpty($worksheetAttributes->getValue($this->resolver));

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNotEmpty($worksheetAttributes->getValue($this->resolver));
    }

    public function testResolveWithComplexNestedStructure(): void
    {
        $spreadsheet = new Spreadsheet();
        $entity = new TestEntityWithNestedCollection();
        $entity->id = 1;
        $entity->name = 'Parent Entity';
        
        $nestedItem = new TestNestedCollectionItem();
        $nestedItem->id = 1;
        $nestedItem->value = 'Nested Item';
        $nestedItem->parentId = 1;
        
        $deepItem = new TestDeepNestedItem();
        $deepItem->id = 1;
        $deepItem->description = 'Deep Item';
        $deepItem->nestedParentId = 1;
        
        $nestedItem->nestedItems = [$deepItem];
        $entity->items = [$nestedItem];
        $objects = [$entity];

        $this->resolver->resolve($spreadsheet, $objects);

        $this->assertNotNull($spreadsheet->getSheetByName('Test Entity With Nested'));
        $this->assertNotNull($spreadsheet->getSheetByName('Nested Items'));
        $this->assertNotNull($spreadsheet->getSheetByName('Deep Nested Items'));
        
        $this->assertCount(4, $spreadsheet->getAllSheets());
    }
}
