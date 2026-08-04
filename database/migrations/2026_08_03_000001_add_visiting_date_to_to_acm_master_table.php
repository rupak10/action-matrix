<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add as nullable first so existing rows are not blocked
        Schema::table('acm_master', function (Blueprint $table) {
            // visiting_date is the FROM date; visiting_date_to is the TO date
            $table->date('visiting_date_to')->nullable()->after('visiting_date');
        });

        // Backfill existing rows: use the from-date as the to-date
        DB::table('acm_master')->whereNull('visiting_date_to')->update([
            'visiting_date_to' => DB::raw('visiting_date'),
        ]);

        // Now enforce NOT NULL
        Schema::table('acm_master', function (Blueprint $table) {
            $table->date('visiting_date_to')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('acm_master', function (Blueprint $table) {
            $table->dropColumn('visiting_date_to');
        });
    }
};
