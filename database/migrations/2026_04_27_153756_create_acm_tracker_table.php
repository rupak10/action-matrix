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
        Schema::create('acm_tracker', function (Blueprint $table) {
            $table->string('po_code', 5);
            $table->decimal('sl', 10, 0);
            $table->timestamp('created_at')->useCurrent();
            
            $table->primary(['po_code', 'sl']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acm_tracker');
    }
};
