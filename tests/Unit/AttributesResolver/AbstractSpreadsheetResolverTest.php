<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Unit\AttributesResolver;

use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetVirtualProperty;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestCollectionItem;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestCollectionParentWithoutId;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntity;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithArray;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithCustomMapping;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithInlineCollection;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithInvalidInlineCollection;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithoutId;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestEntityWithVirtualProperty;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestInlineItem;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\TestNonRootEntity;
use Solitus0\DtoXlsxGenerator\Util\InlineValueSerializer;

class AbstractSpreadsheetResolverTest extends TestCase
{
    private TestSpreadsheetResolver $resolver;

    private InlineValueSerializer $inlineValueSerializer;

    protected function setUp(): void
    {
        $this->inlineValueSerializer = new InlineValueSerializer();
        $this->resolver = new TestSpreadsheetResolver($this->inlineValueSerializer);
    }

    public function testResetInMemoryCache(): void
    {
        $this->resolver->getPropertiesWithAttribute(TestEntity::class, SpreadsheetProperty::class);
        $this->resolver->getAlphabeticColumnIndex(5);
        
        $this->resolver->resetInMemoryCache();
        
        $reflection = new \ReflectionClass($this->resolver);
        $objectProperties = $reflection->getProperty('objectProperties');
        $columnIndexes = $reflection->getProperty('columnIndexes');
        $lastSpreadsheetRowIndex = $reflection->getProperty('lastSpreadsheetRowIndex');
        $worksheetAttributes = $reflection->getProperty('worksheetAttributes');
        $worksheetDepth = $reflection->getProperty('worksheetDepth');

        $this->assertEmpty($objectProperties->getValue($this->resolver));
        $this->assertEmpty($columnIndexes->getValue($this->resolver));
        $this->assertEmpty($lastSpreadsheetRowIndex->getValue($this->resolver));
        $this->assertEmpty($worksheetAttributes->getValue($this->resolver));
        $this->assertEmpty($worksheetDepth->getValue($this->resolver));
    }

    public function testGetSpreadsheetPropertiesWithRootEntity(): void
    {
        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = 'Test Name';
        $entity->description = 'Test Description';
        $entity->createdAt = new DateTime('2023-01-01 10:00:00');

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntity::class,
            $entity
        );

        $this->assertArrayHasKey('A', $properties);
        $this->assertArrayHasKey('B', $properties);
        $this->assertArrayHasKey('C', $properties);

        $this->assertEquals('name', $properties['A']['name']);
        $this->assertEquals('Test Name', $properties['A']['value']);
        $this->assertEquals('description', $properties['B']['name']);
        $this->assertEquals('Test Description', $properties['B']['value']);
        $this->assertEquals('createdAt', $properties['C']['name']);
        $this->assertEquals('2023-01-01 10:00:00', $properties['C']['value']);
    }

    public function testGetSpreadsheetPropertiesWithNonRootEntity(): void
    {
        $entity = new TestNonRootEntity();
        $entity->id = 2;
        $entity->value = 'Test Value';

        $properties = $this->resolver->getSpreadsheetProperties(
            TestNonRootEntity::class,
            $entity,
            'id',
            new TestEntity()
        );

        $this->assertArrayHasKey('B', $properties);
        $this->assertArrayHasKey('C', $properties);

        $this->assertEquals('id', $properties['A']['name']);
        $this->assertEquals('Id', $properties['B']['name']);
        $this->assertEquals(2, $properties['B']['value']);
        $this->assertEquals('value', $properties['C']['name']);
        $this->assertEquals('Test Value', $properties['C']['value']);
    }

    public function testGetSpreadsheetPropertiesWithVirtualProperty(): void
    {
        $entity = new TestEntityWithVirtualProperty();
        $entity->id = 3;
        $entity->firstName = 'John';
        $entity->lastName = 'Doe';

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntityWithVirtualProperty::class,
            $entity
        );

        $this->assertArrayHasKey('A', $properties);
        $this->assertArrayHasKey('B', $properties);

        $this->assertEquals('firstName', $properties['A']['name']);
        $this->assertEquals('John', $properties['A']['value']);
        $this->assertEquals('Full Name', $properties['B']['name']);
        $this->assertEquals('John Doe', $properties['B']['value']);
    }

    public function testGetSpreadsheetPropertiesWithInlineCollection(): void
    {
        $entity = new TestEntityWithInlineCollection();
        $entity->id = 4;
        $entity->name = 'Test Entity';
        
        $item1 = new TestInlineItem();
        $item1->id = 1;
        $item1->name = 'Item 1';
        $item1->value = 'Value 1';
        
        $item2 = new TestInlineItem();
        $item2->id = 2;
        $item2->name = 'Item 2';
        $item2->value = 'Value 2';
        
        $entity->items = [$item1, $item2];

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntityWithInlineCollection::class,
            $entity
        );

        $this->assertArrayHasKey('A', $properties);
        $this->assertArrayHasKey('B', $properties);

        $this->assertEquals('name', $properties['A']['name']);
        $this->assertEquals('Test Entity', $properties['A']['value']);
        $this->assertEquals('items', $properties['B']['name']);
        $this->assertEquals("name: Item 1, value: Value 1\nname: Item 2, value: Value 2", $properties['B']['value']);
    }

    public function testRootDtoWithoutCollectionsDoesNotRequireId(): void
    {
        $entity = new TestEntityWithoutId();
        $entity->name = 'Unnamed';

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntityWithoutId::class,
            $entity
        );

        $this->assertEquals('name', $properties['A']['name']);
        $this->assertEquals('Unnamed', $properties['A']['value']);
    }

    public function testThrowsWhenParentEntityLacksIdForCollection(): void
    {
        $parent = new TestCollectionParentWithoutId();
        $parent->name = 'Parent';

        $child = new TestCollectionItem();
        $child->id = 1;
        $child->value = 'Value';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('does not define property "parentId"');

        $this->resolver->getSpreadsheetProperties(
            TestCollectionItem::class,
            $child,
            'parentId',
            $parent
        );
    }

    public function testThrowsWhenMappedPropertyValueIsNull(): void
    {
        $parent = new TestEntityWithCustomMapping();
        $parent->id = 1;
        $parent->externalCode = null;

        $child = new TestCollectionItem();
        $child->id = 1;
        $child->value = 'value';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must provide a non-null value for property "externalCode"');

        $this->resolver->getSpreadsheetProperties(
            TestCollectionItem::class,
            $child,
            'externalCode',
            $parent
        );
    }

    public function testGetAlphabeticColumnIndex(): void
    {
        $this->assertEquals('B', $this->resolver->getAlphabeticColumnIndex(1));
        $this->assertEquals('C', $this->resolver->getAlphabeticColumnIndex(2));
        $this->assertEquals('D', $this->resolver->getAlphabeticColumnIndex(3));
        $this->assertEquals('AA', $this->resolver->getAlphabeticColumnIndex(26));
        $this->assertEquals('AB', $this->resolver->getAlphabeticColumnIndex(27));
        $this->assertEquals('AC', $this->resolver->getAlphabeticColumnIndex(28));
        $this->assertEquals('BA', $this->resolver->getAlphabeticColumnIndex(52));
        $this->assertEquals('BB', $this->resolver->getAlphabeticColumnIndex(53));
    }

    public function testGetClassName(): void
    {
        $objects = [
            new TestEntity(),
            new TestEntity(),
        ];

        $className = $this->resolver->getClassName($objects);
        $this->assertEquals(TestEntity::class, $className);
    }

    public function testGetClassNameWithEmptyCollection(): void
    {
        $objects = [];

        $className = $this->resolver->getClassName($objects);
        $this->assertNull($className);
    }

    public function testCreateWorksheet(): void
    {
        $spreadsheet = new Spreadsheet();
        $attribute = new SpreadsheetRoot(TestEntity::class, 'Test Sheet');
        $attribute->setSheetIndex(0);

        $worksheet = $this->resolver->createWorksheet($spreadsheet, $attribute);

        $this->assertEquals('Test Sheet', $worksheet->getTitle());
        
        $this->assertEquals('Name', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('Description', $worksheet->getCell('B1')->getValue());
        $this->assertEquals('Created at', $worksheet->getCell('C1')->getValue());
    }

    public function testWriteRows(): void
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Test Sheet');

        $rowsData = [
            [
                'A' => ['name' => 'Id', 'value' => 1],
                'B' => ['name' => 'name', 'value' => 'Test Name'],
            ],
            [
                'A' => ['name' => 'Id', 'value' => 2],
                'B' => ['name' => 'name', 'value' => 'Test Name 2'],
            ],
        ];

        $this->resolver->writeRows($worksheet, $rowsData);

        $this->assertEquals(1, $worksheet->getCell('A2')->getValue());
        $this->assertEquals('Test Name', $worksheet->getCell('B2')->getValue());
        $this->assertEquals(2, $worksheet->getCell('A3')->getValue());
        $this->assertEquals('Test Name 2', $worksheet->getCell('B3')->getValue());
    }

    public function testWriteRowsWithEmptyData(): void
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Test Sheet');

        $initialHighestRow = $worksheet->getHighestRow();

        $this->resolver->writeRows($worksheet, []);

        $this->assertSame($initialHighestRow, $worksheet->getHighestRow());
    }

    public function testGetCellValueWithDateTime(): void
    {
        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = 'Test';
        $entity->description = 'Test Description';
        $entity->createdAt = new DateTime('2023-12-25 15:30:45');

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntity::class,
            $entity
        );

        $this->assertEquals('2023-12-25 15:30:45', $properties['C']['value']);
    }

    public function testGetCellValueWithArray(): void
    {
        $entity = new TestEntityWithArray();
        $entity->id = 1;
        $entity->tags = ['tag1', 'tag2', 'tag3'];

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntityWithArray::class,
            $entity
        );

        $this->assertEquals('tag1,tag2,tag3', $properties['A']['value']);
    }

    public function testGetCellValueWithEmptyValues(): void
    {
        $entity = new TestEntity();
        $entity->id = 1;
        $entity->name = '';
        $entity->description = null;
        $entity->createdAt = new DateTime();

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntity::class,
            $entity
        );

        $expected = $entity->createdAt->format('Y-m-d H:i:s');

        $this->assertArrayHasKey('A', $properties);
        $this->assertEquals('createdAt', $properties['A']['name']);
        $this->assertEquals($expected, $properties['A']['value']);
        $this->assertArrayNotHasKey('B', $properties);
        $this->assertArrayNotHasKey('C', $properties);
    }

    public function testGetWorksheetWithExistingSheet(): void
    {
        $spreadsheet = new Spreadsheet();
        $attribute = new SpreadsheetRoot(TestEntity::class, 'Test Sheet');
        $attribute->setSheetIndex(0);

        $worksheet = $this->resolver->createWorksheet($spreadsheet, $attribute);
        $this->assertEquals('Test Sheet', $worksheet->getTitle());

        $existingWorksheet = $this->resolver->getWorksheet($spreadsheet, $attribute);
        $this->assertSame($worksheet, $existingWorksheet);
    }

    public function testGetWorksheetWithNonExistingSheet(): void
    {
        $spreadsheet = new Spreadsheet();
        $attribute = new SpreadsheetRoot(TestEntity::class, 'Non Existing Sheet');
        $attribute->setSheetIndex(0);

        $worksheet = $this->resolver->getWorksheet($spreadsheet, $attribute);
        $this->assertEquals('Non Existing Sheet', $worksheet->getTitle());
    }

    public function testCreateWorksheetWithNonRootEntity(): void
    {
        $spreadsheet = new Spreadsheet();
        $attribute = new SpreadsheetRoot(TestNonRootEntity::class, 'Non Root Sheet');
        $attribute->setSheetIndex(0);

        $worksheet = $this->resolver->createWorksheet($spreadsheet, $attribute);

        $this->assertEquals('Non Root Sheet', $worksheet->getTitle());
        $this->assertEquals('Id', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('Id', $worksheet->getCell('B1')->getValue());
    }

    public function testGetPropertiesWithAttributeCaching(): void
    {
        $properties1 = $this->resolver->getPropertiesWithAttribute(TestEntity::class, SpreadsheetProperty::class);
        $properties2 = $this->resolver->getPropertiesWithAttribute(TestEntity::class, SpreadsheetProperty::class);

        $this->assertSame($properties1, $properties2);
    }

    public function testGetMethodsWithAttributeCaching(): void
    {
        $methods1 = $this->resolver->getMethodsWithAttribute(TestEntityWithVirtualProperty::class, SpreadsheetVirtualProperty::class);
        $methods2 = $this->resolver->getMethodsWithAttribute(TestEntityWithVirtualProperty::class, SpreadsheetVirtualProperty::class);

        $this->assertSame($methods1, $methods2);
    }

    public function testGetVirtualPropertiesCaching(): void
    {
        $properties1 = $this->resolver->getVirtualProperties(TestEntityWithVirtualProperty::class);
        $properties2 = $this->resolver->getVirtualProperties(TestEntityWithVirtualProperty::class);

        $this->assertSame($properties1, $properties2);
    }

    public function testGetSpreadsheetPropertiesWithEmptyInlineCollection(): void
    {
        $entity = new TestEntityWithInlineCollection();
        $entity->id = 1;
        $entity->name = 'Test Entity';
        $entity->items = [];

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntityWithInlineCollection::class,
            $entity
        );

        $this->assertArrayHasKey('A', $properties);
        $this->assertArrayHasKey('B', $properties);
        $this->assertEquals('items', $properties['B']['name']);
        $this->assertEquals('', $properties['B']['value']);
    }

    public function testGetSpreadsheetPropertiesWithNullInlineCollection(): void
    {
        $entity = new TestEntityWithInlineCollection();
        $entity->id = 1;
        $entity->name = 'Test Entity';
        $entity->items = null;

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntityWithInlineCollection::class,
            $entity
        );

        $this->assertArrayHasKey('A', $properties);
        $this->assertArrayHasKey('B', $properties);
        $this->assertEquals('items', $properties['B']['name']);
        $this->assertEquals('', $properties['B']['value']);
    }

    public function testGetSpreadsheetPropertiesWithInvalidInlineCollection(): void
    {
        $entity = new TestEntityWithInvalidInlineCollection();
        $entity->id = 1;
        $entity->name = 'Test Entity';
        $entity->items = [];

        $properties = $this->resolver->getSpreadsheetProperties(
            TestEntityWithInvalidInlineCollection::class,
            $entity
        );

        $this->assertArrayHasKey('A', $properties);
        $this->assertArrayNotHasKey('B', $properties);
    }
}
