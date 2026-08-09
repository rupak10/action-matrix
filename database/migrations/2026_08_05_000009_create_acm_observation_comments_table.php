<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_observation_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->constrained('acm_observations')->cascadeOnDelete();
            $table->string('comment_source', 10); // PKSF / PO
            $table->text('comment_detail');
            $table->tinyInteger('is_draft')->default(0); // 0=editable/in-progress, 1=finalized/locked
            $table->string('created_by', 20);
            $table->string('updated_by', 20)->nullable();
            $table->timestamps();

            $table->index(['observation_id']);
            $table->index(['observation_id', 'is_draft']);
            $table->index(['observation_id', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_observation_comments');
    }
};
