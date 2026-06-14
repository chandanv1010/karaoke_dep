<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_import_records', function (Blueprint $table) {
            $table->id();
            $table->string('source_table', 100);
            $table->string('source_pk', 191)->nullable();
            $table->longText('payload');
            $table->string('migrated_to_table', 100)->nullable();
            $table->unsignedBigInteger('migrated_to_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['source_table', 'source_pk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_import_records');
    }
};
