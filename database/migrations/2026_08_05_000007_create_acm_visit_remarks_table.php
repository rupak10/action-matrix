<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_visit_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('acm_visits')->cascadeOnDelete();
            $table->foreignId('movement_id')->nullable()->constrained('acm_visit_movements')->nullOnDelete();
            $table->text('remarks');
            $table->string('created_by', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->index('visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_visit_remarks');
    }
};
