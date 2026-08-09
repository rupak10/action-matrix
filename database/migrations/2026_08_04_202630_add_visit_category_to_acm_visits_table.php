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
        Schema::table('acm_visits', function (Blueprint $table) {
            $table->string('visit_category')->nullable()->after('visit_type');
        });
    }

    public function down(): void
    {
        Schema::table('acm_visits', function (Blueprint $table) {
            $table->dropColumn('visit_category');
        });
    }
};
