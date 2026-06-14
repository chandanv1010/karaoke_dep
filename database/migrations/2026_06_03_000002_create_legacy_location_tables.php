<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->unsignedInteger('code')->primary();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->unsignedInteger('code')->primary();
                $table->string('name');
                $table->unsignedInteger('province_code')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wards')) {
            Schema::create('wards', function (Blueprint $table) {
                $table->unsignedInteger('code')->primary();
                $table->string('name');
                $table->unsignedInteger('province_code')->index();
                $table->unsignedInteger('district_code')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wards');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
    }
};
