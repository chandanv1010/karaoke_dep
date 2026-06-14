<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class SchemaCache
{
    private static array $columns = [];
    private static array $tables = [];

    public static function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";

        if (!array_key_exists($key, self::$columns)) {
            self::$columns[$key] = Schema::hasColumn($table, $column);
        }

        return self::$columns[$key];
    }

    public static function hasTable(string $table): bool
    {
        if (!array_key_exists($table, self::$tables)) {
            self::$tables[$table] = Schema::hasTable($table);
        }

        return self::$tables[$table];
    }
}
