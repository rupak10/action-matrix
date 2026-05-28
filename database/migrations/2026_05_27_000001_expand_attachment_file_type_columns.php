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
        Schema::table('acm_master_file_attachment', function (Blueprint $table) {
            $table->string('file_type', 255)->nullable()->change();
        });

        Schema::table('acm_comments_file_attachment', function (Blueprint $table) {
            $table->string('file_type', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acm_master_file_attachment', function (Blueprint $table) {
            $table->string('file_type', 20)->nullable()->change();
        });

        Schema::table('acm_comments_file_attachment', function (Blueprint $table) {
            $table->string('file_type', 20)->nullable()->change();
        });
    }
};
