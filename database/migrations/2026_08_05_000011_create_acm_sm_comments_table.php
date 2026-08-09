<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_sm_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('acm_visits')->cascadeOnDelete();
            $table->string('emp_id', 20)->index();
            $table->text('comment');
            $table->timestamps();

            $table->index('visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_sm_comments');
    }
};
