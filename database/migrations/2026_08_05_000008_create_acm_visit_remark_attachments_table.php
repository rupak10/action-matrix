<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_visit_remark_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remark_id')->constrained('acm_visit_remarks')->cascadeOnDelete();
            $table->string('file_name', 400);
            $table->string('file_path', 1000);
            $table->string('file_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('created_by', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->index('remark_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_visit_remark_attachments');
    }
};
