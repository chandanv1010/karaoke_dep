<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            $table->integer('total_applications')->default(0)->after('id');
            
            $table->unsignedBigInteger('train_id')->after('major_catalogue_id'); 
            $table->foreign('train_id')->references('id')->on('scholar_trains')->onDelete('cascade'); 
            
            $table->unsignedBigInteger('major_group_id')->nullable()->after('train_id');
            $table->foreign('major_group_id')->references('id')->on('major_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            $table->dropForeign(['train_id']);
            $table->dropForeign(['major_group_id']);
            $table->dropColumn(['total_applications', 'train_id', 'major_group_id']);
        });
    }
};
