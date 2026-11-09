<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * "Published" flags of the old settings forms (News popup `isPublished`,
 * NewsBar `isNewsBar`), 2026-11-03.
 *
 * The old Blade forms stored the checkbox value 'on' / NULL, although the
 * migrations declare boolean columns. These helpers read both and write what
 * the real column accepts, so nothing depends on which one the DB has.
 */
class PublishedFlag
{
    public static function isOn($value): bool
    {
        return $value === 'on' || $value === true || $value === 1 || $value === '1';
    }

    /** 1/0 for a numeric/boolean column, else 'on'/NULL like the old forms. */
    public static function valueFor(string $table, string $column, bool $on)
    {
        try {
            $type = strtolower((string) Schema::getColumnType($table, $column));
        } catch (\Throwable $e) {
            $type = '';
        }

        if (in_array($type, ['tinyint', 'boolean', 'bool', 'bit', 'smallint', 'int', 'integer'], true)) {
            return $on ? 1 : 0;
        }

        return $on ? 'on' : null;
    }

    /** Char length of a text column (varchar(255) -> 255), null for TEXT, $default if unknown. */
    public static function maxLength(string $table, string $column, ?int $default = 255): ?int
    {
        try {
            foreach (Schema::getColumns($table) as $col) {
                if ($col['name'] === $column) {
                    return preg_match('/char\((\d+)\)/i', (string) $col['type'], $m) ? (int) $m[1] : null;
                }
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return $default;
    }
}
