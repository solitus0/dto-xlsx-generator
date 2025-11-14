<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Tests\Fixtures;

final class CurrencyAmount
{
    public function __construct(
        public readonly float $value,
        public readonly string $currency,
    ) {
    }

    public function formatWithCurrency(): string
    {
        return sprintf('%.2f %s', $this->value, $this->currency);
    }
}
