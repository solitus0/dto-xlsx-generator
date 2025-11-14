<?php

declare(strict_types=1);

namespace Solitus0\DtoXlsxGenerator\Util;

final class ArrayPropertyUtil
{
    private function __construct()
    {
    }

    /**
     * @param array<string, mixed>|mixed $data
     */
    public static function getProperty($data, string|int $key, mixed $default = null): mixed
    {
        if (!is_array($data)) {
            return $default;
        }

        return array_key_exists($key, $data) && $data[$key] !== null ? $data[$key] : $default;
    }
}
