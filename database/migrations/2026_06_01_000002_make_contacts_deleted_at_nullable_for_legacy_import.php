<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'deleted_at')) {
            DB::statement('ALTER TABLE `contacts` MODIFY `deleted_at` timestamp NULL DEFAULT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'deleted_at')) {
            DB::statement('ALTER TABLE `contacts` MODIFY `deleted_at` timestamp NOT NULL');
        }
    }
};
