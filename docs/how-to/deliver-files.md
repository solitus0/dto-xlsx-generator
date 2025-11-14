# How-to · Deliver XLSX Files

After calling `SpreadsheetGenerator::generate()`, choose how you want to hand the spreadsheet over to users or downstream systems.

## Save to disk (CLI jobs, cron tasks)

```php
$spreadsheet = $generator->generate($reports);
$writer = XlsxWriterFactory::create($spreadsheet);
$target = sprintf('/tmp/dtoxlsx/reports_%s.xlsx', date('Ymd_His'));
$writer->save($target);
```

Tips:
- Use `/tmp/dtoxlsx` for transient files; it is gitignored per project policy.
- Move the file to durable storage (S3, FTP, etc.) once the write succeeds.

## Stream as an HTTP download

```php
$spreadsheet = $generator->generate($reports);
$writer = XlsxWriterFactory::create($spreadsheet);

return new StreamedResponse(function () use ($writer) {
    $writer->save('php://output');
}, 200, [
    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'Content-Disposition' => 'attachment; filename="reports.xlsx"',
]);
```

## Attach to emails or queues

1. Save the file to a temp path using the disk approach.
2. Read the bytes with `file_get_contents()`.
3. Attach them to your mailer or encode them for transport (e.g., base64 for message queues).

## Reusing the same spreadsheet instance

`SpreadsheetGenerator::generate()` accepts an existing `Spreadsheet` object. Pass the previous instance when batching multiple exports to share styles or metadata:

```php
$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

foreach ($chunkedReports as $reports) {
    $spreadsheet = $generator->generate($reports, $spreadsheet);
}
```

Finish by writing the accumulated workbook through the factory. This pattern keeps memory usage predictable in long-running workers.
