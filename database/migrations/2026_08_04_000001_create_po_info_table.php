<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_info', function (Blueprint $table) {
            $table->id();
            $table->string('po_code')->unique()->notNull();
            $table->string('po_short_name')->nullable();
            $table->string('po_name')->notNull();
            $table->char('is_active', 1)->default('Y');
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_info');
    }
};
