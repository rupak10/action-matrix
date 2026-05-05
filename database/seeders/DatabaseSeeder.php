<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'rupak.pksf@gmail.com'),
        ], [
            'name' => env('ADMIN_NAME', 'Rupak'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'rupak123')),
            'emp_id' => '105257',
            'designation' => 'Assistant Manager',
            'dept_id' => 'IT-01',
            'dept_name' => 'Information Technology',
            'unit_id' => 'UNIT-A',
            'unit_name' => 'Core Development',
        ]);
    }
}
