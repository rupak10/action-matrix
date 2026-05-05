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
        Schema::table('acm_master', function (Blueprint $table) {
            $table->string('current_desk_emp_id')->nullable()->after('po_inbox');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acm_master', function (Blueprint $table) {
            $table->dropColumn('current_desk_emp_id');
        });
    }
};
