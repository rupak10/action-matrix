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
        // Drop the old movement table
        Schema::dropIfExists('acm_movement');

        // 1. PKSF Internal Movements
        Schema::create('acm_pksf_movements', function (Blueprint $table) {
            $table->string('acm_id');
            $table->integer('sl');
            $table->string('from_emp_id');
            $table->string('to_emp_id');
            $table->string('action_type');
            $table->text('remarks')->nullable();
            $table->string('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['acm_id', 'sl']);
        });

        // 2. PO Internal Movements
        Schema::create('acm_po_movements', function (Blueprint $table) {
            $table->string('acm_id');
            $table->integer('sl');
            $table->string('from_emp_id');
            $table->string('to_emp_id');
            $table->string('action_type');
            $table->text('remarks')->nullable();
            $table->string('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['acm_id', 'sl']);
        });

        // 3. Discussion Thread (PKSF <-> PO)
        Schema::create('acm_discussions', function (Blueprint $table) {
            $table->string('acm_id');
            $table->integer('sl');
            $table->enum('party_type', ['PKSF', 'PO']);
            $table->string('emp_id');
            $table->text('comments');
            $table->string('attachment_path')->nullable();
            $table->date('visiting_date')->nullable();
            $table->string('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['acm_id', 'sl']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acm_pksf_movements');
        Schema::dropIfExists('acm_po_movements');
        Schema::dropIfExists('acm_discussions');
    }
};
