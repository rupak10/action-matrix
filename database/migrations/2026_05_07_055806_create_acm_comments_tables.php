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
        // 1. Comments table
        Schema::create('acm_comments', function (Blueprint $table) {
            $table->string('acm_id', 60);
            $table->integer('sl');
            
            $table->string('comment_source', 20); // PKSF/PO
            $table->string('comment_short', 20)->nullable(); // EKMOT/DIMOT
            $table->text('comment_detail');
            
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            
            $table->primary(['acm_id', 'sl']);
            $table->foreign('acm_id')->references('acm_id')->on('acm_master')->onDelete('cascade');
        });

        // 2. File attachment table for comments
        Schema::create('acm_comments_file_attachment', function (Blueprint $table) {
            $table->string('acm_id', 60);
            $table->integer('sl');
            $table->integer('file_id');
            
            $table->string('file_upload_by', 20)->nullable(); // PO/PKSF
            $table->string('file_name', 400);
            $table->string('file_path', 1000);
            $table->string('file_type', 20)->nullable();
            $table->integer('file_size')->nullable(); 
            
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->primary(['acm_id', 'sl', 'file_id']);
            $table->foreign(['acm_id', 'sl'])->references(['acm_id', 'sl'])->on('acm_comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acm_comments_file_attachment');
        Schema::dropIfExists('acm_comments');
    }
};
