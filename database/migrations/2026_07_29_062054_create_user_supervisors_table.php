<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_supervisors', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('supervisor_emp_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['emp_id', 'supervisor_emp_id']);
            $table->foreign('emp_id')->references('emp_id')->on('users')->onDelete('cascade');
            $table->foreign('supervisor_emp_id')->references('emp_id')->on('users')->onDelete('cascade');
        });

        // Migrate existing supervisor assignments from users table
        $existing = DB::table('users')
            ->whereNotNull('supervisor_emp_id')
            ->select('emp_id', 'supervisor_emp_id')
            ->get();

        foreach ($existing as $row) {
            DB::table('user_supervisors')->insert([
                'emp_id'            => $row->emp_id,
                'supervisor_emp_id' => $row->supervisor_emp_id,
                'is_primary'        => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('supervisor_emp_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('supervisor_emp_id')->nullable();
        });

        // Restore primary supervisor back to users table
        $primaries = DB::table('user_supervisors')->where('is_primary', true)->get();
        foreach ($primaries as $row) {
            DB::table('users')->where('emp_id', $row->emp_id)
                ->update(['supervisor_emp_id' => $row->supervisor_emp_id]);
        }

        Schema::dropIfExists('user_supervisors');
    }
};
