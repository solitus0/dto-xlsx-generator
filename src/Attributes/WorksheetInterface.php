<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Attributes;

interface WorksheetInterface
{
    public function getClassName(): string;

    public function getSheetName(): string;

    public function getMappedBy(): ?string;

    public function getSheetIndex(): ?int;

    public function setSheetIndex(?int $sheetIndex): self;

    public function getDepth(): ?int;

    public function setDepth(?int $depth): self;
}
