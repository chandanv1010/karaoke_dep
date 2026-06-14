<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class SchemaCache
{
    private static array $columns = [];
    private static array $tables = [];

    public static function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";

        if (!array_key_exists($key, self::$columns)) {
            self::$columns[$key] = Cache::rememberForever("schema_col_{$table}_{$column}", function () use ($table, $column) {
                return Schema::hasColumn($table, $column);
            });
        }

        return self::$columns[$key];
    }

    public static function hasTable(string $table): bool
    {
        if (!array_key_exists($table, self::$tables)) {
            self::$tables[$table] = Cache::rememberForever("schema_table_{$table}", function () use ($table) {
                return Schema::hasTable($table);
            });
        }

        return self::$tables[$table];
    }
}
