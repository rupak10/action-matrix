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
        Schema::create('acm_master_file_attachment', function (Blueprint $table) {
            $table->string('acm_id', 60);
            $table->decimal('sl', 10, 0);
            
            $table->string('file_name', 400)->nullable();
            $table->string('file_path', 1000);
            $table->string('file_type', 20)->nullable();
            $table->decimal('file_size', 15, 0)->nullable();
            
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['acm_id', 'sl']);
            $table->foreign('acm_id')->references('acm_id')->on('acm_master')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acm_master_file_attachment');
    }
};
