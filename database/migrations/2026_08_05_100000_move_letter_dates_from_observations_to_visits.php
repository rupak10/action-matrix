<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add letter dates to visits (they belong to the visit, not individual observations)
        Schema::table('acm_visits', function (Blueprint $table) {
            $table->date('letter_issue_date')->nullable()->after('visit_category');
            $table->date('letter_response_date')->nullable()->after('letter_issue_date');
        });

        // Remove letter dates from observations
        Schema::table('acm_observations', function (Blueprint $table) {
            $table->dropColumn(['letter_issue_date', 'letter_response_date']);
        });
    }

    public function down(): void
    {
        Schema::table('acm_observations', function (Blueprint $table) {
            $table->date('letter_issue_date')->nullable();
            $table->date('letter_response_date')->nullable();
        });

        Schema::table('acm_visits', function (Blueprint $table) {
            $table->dropColumn(['letter_issue_date', 'letter_response_date']);
        });
    }
};
