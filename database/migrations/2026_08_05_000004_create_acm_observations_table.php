<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('acm_visits')->cascadeOnDelete();
            $table->string('observation_category', 50); // FINANCIAL / OPERATIONAL / COMPLIANCE / GOVERNANCE
            $table->text('pksf_observation');
            $table->text('direction_to_po');
            $table->string('priority', 10)->default('MEDIUM'); // LOW / MEDIUM / HIGH
            $table->char('action_matrix', 1)->default('N'); // Y / N
            $table->date('letter_issue_date')->nullable();
            $table->date('letter_response_date')->nullable();
            $table->string('resolution_status', 30)->default('OPEN'); // OPEN / PENDING_RESOLVED / RESOLVED
            $table->string('created_by', 20);
            $table->string('updated_by', 20)->nullable();
            $table->timestamps();

            $table->index(['visit_id']);
            $table->index(['visit_id', 'resolution_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_observations');
    }
};
