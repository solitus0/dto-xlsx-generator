<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Util;

class InlineValueSerializer
{
    public function serialize(array $inlineValues): string
    {
        $lineValue = array_map(fn ($inlineValue) => implode(', ', $inlineValue), $inlineValues);

        return implode("\n", $lineValue);
    }
}
