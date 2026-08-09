<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_visit_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('acm_visits')->cascadeOnDelete();
            $table->string('movement_side', 10); // PKSF / PO
            $table->string('from_emp_id', 20);
            $table->string('to_emp_id', 20);
            $table->string('action_type', 80);
            $table->text('remarks')->nullable();
            $table->string('created_by', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['visit_id']);
            $table->index(['visit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_visit_movements');
    }
};
