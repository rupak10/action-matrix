<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_code', 60)->unique();
            $table->string('po_code', 5)->index();
            $table->date('visit_from_date');
            $table->date('visit_to_date');
            $table->string('visit_type', 20); // ONSITE / OFFSITE
            $table->string('observation_dept', 100)->nullable();
            $table->string('status', 60)->default('SAVED')->index();
            $table->string('current_desk_emp_id', 20)->nullable()->index();
            $table->string('created_by', 20);
            $table->string('updated_by', 20)->nullable();
            $table->timestamps();

            $table->index(['status', 'po_code']);
            $table->index(['created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_visits');
    }
};
