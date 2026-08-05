<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP TABLE IF EXISTS acm_comments_file_attachment CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_comments CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_master_file_attachment CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_discussions CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_po_movements CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_pksf_movements CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_master CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_tracker CASCADE');
        DB::statement('DROP TABLE IF EXISTS acm_sm_comments CASCADE');
    }

    public function down(): void
    {
        // Irreversible — data was dummy/dev only
    }
};
