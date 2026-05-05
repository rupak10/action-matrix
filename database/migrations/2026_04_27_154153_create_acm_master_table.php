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
        Schema::create('acm_master', function (Blueprint $table) {
            $table->string('acm_id', 60);
            $table->string('po_code', 5);
            $table->date('visiting_date');
            
            $table->string('observation_dept', 60);
            $table->string('observation_category', 500);
            $table->string('visit_type', 20);
            $table->string('visit_category', 60);
            
            $table->date('letter_issue_date')->nullable();
            $table->date('letter_response_date')->nullable();
            
            $table->text('pksf_observation');
            $table->text('direction_to_po');
            
            $table->char('action_matrix', 1)->nullable();
            $table->char('po_inbox', 1)->nullable();
            $table->char('is_editable_by_po', 1)->nullable();
            $table->string('priority', 10)->nullable();
            
            $table->string('status', 60)->nullable();
            $table->string('resolution_status', 60)->nullable();
            
            $table->string('created_by', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by', 20)->nullable();
            $table->timestamp('updated_at')->nullable();
            
            $table->primary('acm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acm_master');
    }
};
