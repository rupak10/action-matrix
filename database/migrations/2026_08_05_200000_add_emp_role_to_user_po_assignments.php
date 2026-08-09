<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_po_assignments', function (Blueprint $table) {
            $table->enum('emp_role', ['CO', 'SO', 'MGT'])->default('CO')->after('po_code');
        });
    }

    public function down(): void
    {
        Schema::table('user_po_assignments', function (Blueprint $table) {
            $table->dropColumn('emp_role');
        });
    }
};
