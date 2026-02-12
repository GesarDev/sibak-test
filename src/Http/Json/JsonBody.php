<?php

namespace App\Http\Json;

use Symfony\Component\HttpFoundation\Request;

final class JsonBody
{
    /** @return array<string,mixed> */
    public static function parse(Request $request): array
    {
        $raw = $request->getContent();
        if ($raw === '' || $raw === null) {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function str(array $data, string $key): string
    {
        $v = $data[$key] ?? '';
        return is_string($v) ? trim($v) : trim((string)$v);
    }
}
