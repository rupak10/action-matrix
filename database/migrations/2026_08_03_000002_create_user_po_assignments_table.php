<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_po_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('po_code');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['emp_id', 'po_code']);
            $table->index('emp_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_po_assignments');
    }
};
