<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetInlineCollection;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetProperty;
use Solitus0\DtoXlsxGenerator\Attributes\SpreadsheetRoot;
use Solitus0\DtoXlsxGenerator\Validator\DtoConfigurationValidator;

class DtoConfigurationValidatorTest extends TestCase
{
    private DtoConfigurationValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new DtoConfigurationValidator();
    }

    public function testValidateDetectsMissingSpreadsheetRoot(): void
    {
        $errors = $this->validator->validate(MissingRootDto::class);

        $this->assertContains(
            sprintf('DTO "%s" must declare the #[SpreadsheetRoot] attribute.', MissingRootDto::class),
            $errors
        );
    }

    public function testValidateDetectsMissingSpreadsheetMembers(): void
    {
        $errors = $this->validator->validate(RootWithoutMembersDto::class);

        $this->assertContains(
            sprintf('DTO "%s" must declare at least one spreadsheet-mapped property or method.', RootWithoutMembersDto::class),
            $errors
        );
    }

    public function testValidateDetectsInvalidCollectionTarget(): void
    {
        $errors = $this->validator->validate(RootWithInvalidCollectionDto::class);

        $this->assertContains(
            sprintf(
                'DTO "%s" uses #[SpreadsheetCollection] on "items" but target class "%s" has no spreadsheet attributes.',
                RootWithInvalidCollectionDto::class,
                CollectionItemWithoutAttributes::class
            ),
            $errors
        );
    }

    public function testValidateDetectsInlineCollectionWithInvalidItemClass(): void
    {
        $errors = $this->validator->validate(RootWithInlineInvalidItemsDto::class);

        $this->assertContains(
            sprintf(
                'DTO "%s" uses #[SpreadsheetInlineCollection] on "items" but target class "%s" has no spreadsheet attributes.',
                RootWithInlineInvalidItemsDto::class,
                InlineItemWithoutAttributes::class
            ),
            $errors
        );
    }

    public function testValidateDetectsNestedCollectionErrors(): void
    {
        $errors = $this->validator->validate(RootWithNestedInvalidCollectionDto::class);

        $this->assertContains(
            sprintf(
                'DTO "%s" uses #[SpreadsheetCollection] on "comments" but target class "%s" has no spreadsheet attributes.',
                NestedChildDto::class,
                NestedLeafWithoutAttributes::class
            ),
            $errors
        );
    }

    public function testValidateReturnsNoErrorsForValidConfiguration(): void
    {
        $errors = $this->validator->validate(ValidRootDto::class);

        $this->assertSame([], $errors);
    }

    public function testValidateDetectsDuplicateSpreadsheetCollectionSheetNames(): void
    {
        $errors = $this->validator->validate(RootWithDuplicateSheetNamesDto::class);

        $this->assertContains(
            sprintf(
                'DTO "%s" uses #[SpreadsheetCollection] on "secondDetails" but sheet name "Details" is already used by "firstDetails".',
                RootWithDuplicateSheetNamesDto::class
            ),
            $errors
        );
    }
}

class MissingRootDto
{
    #[SpreadsheetProperty]
    public string $title = '';
}

#[SpreadsheetRoot(RootWithoutMembersDto::class, 'Root Without Members')]
class RootWithoutMembersDto
{
    public string $title = '';
}

#[SpreadsheetRoot(RootWithInvalidCollectionDto::class, 'Root With Collection')]
class RootWithInvalidCollectionDto
{
    #[SpreadsheetCollection(CollectionItemWithoutAttributes::class, mappedBy: 'rootId', sheetName: 'Items')]
    public array $items = [];

    #[SpreadsheetProperty]
    public string $title = '';
}

class CollectionItemWithoutAttributes
{
    public string $value = '';
}

#[SpreadsheetRoot(ValidRootDto::class, 'Valid Root')]
class ValidRootDto
{
    #[SpreadsheetProperty]
    public string $title = '';

    #[SpreadsheetCollection(ValidCollectionItem::class, mappedBy: 'rootId', sheetName: 'Items')]
    public array $items = [];
}

class ValidCollectionItem
{
    #[SpreadsheetProperty]
    public string $value = '';
}

#[SpreadsheetRoot(RootWithInlineInvalidItemsDto::class, 'Root With Inline Invalid Items')]
class RootWithInlineInvalidItemsDto
{
    #[SpreadsheetProperty]
    public string $title = '';

    #[SpreadsheetInlineCollection(InlineItemWithoutAttributes::class)]
    public array $items = [];
}

class InlineItemWithoutAttributes
{
    public string $value = '';
}

#[SpreadsheetRoot(RootWithNestedInvalidCollectionDto::class, 'Root With Nested Collection')]
class RootWithNestedInvalidCollectionDto
{
    #[SpreadsheetProperty]
    public string $title = '';

    #[SpreadsheetCollection(NestedChildDto::class, mappedBy: 'rootId', sheetName: 'Children')]
    public array $children = [];
}

class NestedChildDto
{
    #[SpreadsheetProperty]
    public string $label = '';

    #[SpreadsheetCollection(NestedLeafWithoutAttributes::class, mappedBy: 'childId', sheetName: 'Comments')]
    public array $comments = [];
}

class NestedLeafWithoutAttributes
{
    public string $body = '';
}

#[SpreadsheetRoot(RootWithDuplicateSheetNamesDto::class, 'Root With Duplicate Sheet Names')]
class RootWithDuplicateSheetNamesDto
{
    #[SpreadsheetProperty]
    public string $title = '';

    #[SpreadsheetCollection(DuplicateSheetChildDto::class, mappedBy: 'rootId', sheetName: 'Details')]
    public array $firstDetails = [];

    #[SpreadsheetCollection(DuplicateSheetChildDto::class, mappedBy: 'rootId', sheetName: 'Details')]
    public array $secondDetails = [];
}

class DuplicateSheetChildDto
{
    #[SpreadsheetProperty]
    public string $value = '';
}
