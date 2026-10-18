<?php

namespace App\Support;

class Term
{
    // "IV" => 4
    public static function number(?string $roman): int
    {
        $map = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000];
        $roman = strtoupper(trim((string) $roman));
        $total = 0;

        for ($i = 0, $n = strlen($roman); $i < $n; $i++) {
            $value = $map[$roman[$i]] ?? 0;
            $next = $map[$roman[$i + 1] ?? ''] ?? 0;
            $total += $value < $next ? -$value : $value;
        }

        return $total;
    }

    // "First Semester" => "First"
    public static function base(?string $name): string
    {
        return trim(preg_replace('/\b(semester|year)\b/i', '', (string) $name));
    }

    // "First" + semester => "First Semester", "First" + year => "First Year"
    public static function label(?string $name, ?string $structure): string
    {
        $base = self::base($name);

        return trim($base . ' ' . ($structure === 'year' ? 'Year' : 'Semester'));
    }
}