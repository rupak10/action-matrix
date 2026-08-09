<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acm_visit_tracker', function (Blueprint $table) {
            $table->string('po_code', 5)->primary();
            $table->unsignedInteger('sl')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acm_visit_tracker');
    }
};
