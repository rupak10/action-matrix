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
        Schema::create('emp_supervisors', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id', 20)->unique();
            $table->string('supervisor_id', 20);
            $table->string('created_by', 20)->nullable();
            $table->timestamps();
            
            $table->foreign('emp_id')->references('emp_id')->on('users')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('emp_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_supervisors');
    }
};
