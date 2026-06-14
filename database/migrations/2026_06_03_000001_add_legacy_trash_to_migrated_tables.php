<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'posts',
        'post_catalogues',
        'products',
        'product_catalogues',
        'attributes',
        'attribute_catalogues',
        'contacts',
        'customers',
        'customer_catalogues',
        'users',
        'user_catalogues',
        'menus',
        'menu_catalogues',
        'slides',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'trash')) {
                    $table->tinyInteger('trash')->default(0);
                }

                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable();
                }
            });

            if (Schema::hasColumn($tableName, 'deleted_at') && DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `deleted_at` timestamp NULL DEFAULT NULL");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'trash')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('trash');
            });
        }
    }
};
