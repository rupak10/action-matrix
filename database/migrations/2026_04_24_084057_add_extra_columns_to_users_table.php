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
        Schema::table('users', function (Blueprint $table) {
            $table->string('emp_id')->unique()->after('email');
            $table->string('designation')->nullable()->after('emp_id');
            $table->string('dept_id')->nullable()->after('designation');
            $table->string('dept_name')->nullable()->after('dept_id');
            $table->string('unit_id')->nullable()->after('dept_name');
            $table->string('unit_name')->nullable()->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['emp_id', 'designation', 'dept_id', 'dept_name', 'unit_id', 'unit_name']);
        });
    }
};
