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
            $table->string('acm_id');
            $table->string('emp_id');
            $table->text('comment');
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index('acm_id');
            $table->index('emp_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_sm_comments');
    }
};
