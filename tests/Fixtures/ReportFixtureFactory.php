<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

use DateTimeImmutable;

final class ReportFixtureFactory
{
    public static function createReport(): TestReportDto
    {
        $report = new TestReportDto();
        $report->id = 42;
        $report->title = 'Quarterly Quality Report';
        $report->owner = 'Operations';
        $report->generatedAt = new DateTimeImmutable('2024-09-10 08:30:00');
        $report->total = new CurrencyAmount(1520.5, 'USD');
        $report->regions = ['North America', 'EMEA'];
        $report->published = true;
        $report->tags = [
            self::createTag('Priority', 'Red'),
            self::createTag('Audit', 'Blue'),
        ];

        $firstLine = self::createLine(
            9001,
            $report->id,
            'Licenses',
            725.25,
            [
                self::createComment(
                    5001,
                    9001,
                    'QA',
                    'Reconcile source data',
                    [
                        self::createFlag(7001, 5001, 'Action', 'Follow-up due 2024-10-01'),
                        self::createFlag(7002, 5001, 'External', 'Needs vendor acknowledgement'),
                    ]
                ),
            ]
        );

        $secondLine = self::createLine(
            9002,
            $report->id,
            'Support',
            795.25,
            [
                self::createComment(
                    5002,
                    9002,
                    'Ops',
                    'Escalation #42 closed',
                    [
                        self::createFlag(7003, 5002, 'Info', 'Ops team aware'),
                    ]
                ),
            ]
        );

        $report->lines = [$firstLine, $secondLine];

        return $report;
    }

    private static function createLine(
        int $id,
        int $reportId,
        string $category,
        float $amount,
        array $comments
    ): TestReportLineDto {
        $line = new TestReportLineDto();
        $line->id = $id;
        $line->reportId = $reportId;
        $line->category = $category;
        $line->amount = $amount;
        $line->comments = $comments;

        return $line;
    }

    /**
     * @param array<TestReportCommentFlagDto> $flags
     */
    private static function createComment(
        int $id,
        int $lineId,
        string $author,
        string $message,
        array $flags
    ): TestReportLineCommentDto {
        $comment = new TestReportLineCommentDto();
        $comment->id = $id;
        $comment->lineId = $lineId;
        $comment->author = $author;
        $comment->message = $message;
        $comment->flags = $flags;

        return $comment;
    }

    private static function createFlag(
        int $id,
        int $commentId,
        string $type,
        string $note
    ): TestReportCommentFlagDto {
        $flag = new TestReportCommentFlagDto();
        $flag->id = $id;
        $flag->commentId = $commentId;
        $flag->type = $type;
        $flag->note = $note;

        return $flag;
    }

    private static function createTag(string $label, string $color): TestReportTag
    {
        return new TestReportTag($label, $color);
    }
}
