<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Solitus0\DtoXlsxGenerator\AttributesResolver\SpreadsheetResolverInterface;

class SpreadsheetGenerator
{
    /**
     * @var SpreadsheetResolverInterface[]
     */
    private array $attributeResolvers = [];

    /**
     * @param iterable<SpreadsheetResolverInterface> $resolvers
     */
    public function __construct(
        iterable $resolvers,
    ) {
        foreach ($resolvers as $resolver) {
            $this->attributeResolvers[] = $resolver;
        }
    }

    public function generate(iterable $objects, ?Spreadsheet $spreadsheet = null): Spreadsheet
    {
        if (!$spreadsheet) {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);
        }

        usort($this->attributeResolvers, static function ($r1, $r2) {
            return ($r1::getPriority() > $r2::getPriority()) ? -1 : 1;
        });

        foreach ($this->attributeResolvers as $resolver) {
            $resolver->resetInMemoryCache();
            $resolver->resolve($spreadsheet, $objects);
        }

        return $spreadsheet;
    }
}
