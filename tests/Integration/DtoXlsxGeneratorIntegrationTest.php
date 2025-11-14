<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Integration;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Solitus0\DtoXlsxGenerator\Tests\Assertions\WorksheetAssert;
use Solitus0\DtoXlsxGenerator\Tests\Fixtures\ReportFixtureFactory;

final class DtoXlsxGeneratorIntegrationTest extends IntegrationTestCase
{
    public function test_bundle_generates_spreadsheet_for_attribute_decorated_dtos(): void
    {
        $report = ReportFixtureFactory::createReport();

        $spreadsheet = $this->resolveSpreadsheetAttributesUseCase()->generate([$report]);

        $this->assertReportsSheet($spreadsheet);
        $this->assertReportLinesSheet($spreadsheet);
        $this->assertReportLineCommentsSheet($spreadsheet);
        $this->assertReportCommentFlagsSheet($spreadsheet);
    }

    private function assertReportsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->getWorksheet($spreadsheet, 'Reports');

        WorksheetAssert::assertRowEquals($this, $sheet, 1, [
            'A' => 'Title',
            'B' => 'Owner',
            'C' => 'Generated at',
            'D' => 'Total',
            'E' => 'Regions',
            'F' => 'Lifecycle status',
            'G' => 'Tags',
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 2, [
            'A' => 'Quarterly Quality Report',
            'B' => 'Operations',
            'C' => '2024-09-10 08:30:00',
            'D' => '1520.50 USD',
            'E' => 'North America,EMEA',
            'F' => 'Published',
        ]);

        $expectedTags = "label: Priority, color: Red\nlabel: Audit, color: Blue";
        WorksheetAssert::assertRowEquals($this, $sheet, 2, ['G' => $expectedTags]);
    }

    private function assertReportLinesSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->getWorksheet($spreadsheet, 'Report Line Items');

        WorksheetAssert::assertRowEquals($this, $sheet, 1, [
            'A' => 'Test Report Dto Id',
            'B' => 'Id',
            'C' => 'Category',
            'D' => 'Amount',
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 2, [
            'A' => 42,
            'B' => 9001,
            'C' => 'Licenses',
            'D' => 725.25,
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 3, [
            'A' => 42,
            'B' => 9002,
            'C' => 'Support',
            'D' => 795.25,
        ]);
    }

    private function assertReportLineCommentsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->getWorksheet($spreadsheet, 'Report Line Comments');

        WorksheetAssert::assertRowEquals($this, $sheet, 1, [
            'A' => 'Test Report Line Dto Id',
            'B' => 'Id',
            'C' => 'Author',
            'D' => 'Message',
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 2, [
            'A' => 9001,
            'B' => 5001,
            'C' => 'QA',
            'D' => 'Reconcile source data',
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 3, [
            'A' => 9002,
            'B' => 5002,
            'C' => 'Ops',
            'D' => 'Escalation #42 closed',
        ]);
    }

    private function assertReportCommentFlagsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->getWorksheet($spreadsheet, 'Report Comment Flags');

        WorksheetAssert::assertRowEquals($this, $sheet, 1, [
            'A' => 'Test Report Line Comment Dto Id',
            'B' => 'Id',
            'C' => 'Type',
            'D' => 'Note',
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 2, [
            'A' => 5001,
            'B' => 7001,
            'C' => 'Action',
            'D' => 'Follow-up due 2024-10-01',
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 3, [
            'A' => 5001,
            'B' => 7002,
            'C' => 'External',
            'D' => 'Needs vendor acknowledgement',
        ]);

        WorksheetAssert::assertRowEquals($this, $sheet, 4, [
            'A' => 5002,
            'B' => 7003,
            'C' => 'Info',
            'D' => 'Ops team aware',
        ]);
    }

    private function getWorksheet(Spreadsheet $spreadsheet, string $name): Worksheet
    {
        $worksheet = $spreadsheet->getSheetByName($name);
        $this->assertNotNull($worksheet, sprintf('Worksheet "%s" should exist', $name));

        return $worksheet;
    }
}
