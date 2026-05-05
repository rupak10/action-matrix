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
        Schema::create('acm_movement', function (Blueprint $table) {
            $table->string('acm_id', 60);
            $table->decimal('sl', 10, 0);
            
            $table->string('from_party', 20);
            $table->string('item_from', 20)->nullable();
            
            $table->string('to_party', 20);
            $table->string('item_to', 20)->nullable();
            
            $table->string('action_type', 60)->nullable();
            $table->text('remarks')->nullable();
            
            $table->string('status', 50)->default('RUNNING');
            
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 20)->nullable();

            $table->primary(['acm_id', 'sl']);
            $table->foreign('acm_id')->references('acm_id')->on('acm_master')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acm_movement');
    }
};
