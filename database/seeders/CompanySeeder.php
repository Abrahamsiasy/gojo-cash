<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            DB::table('companies')->insert([
                'name' => 'Example Company '.$i,
                'slug' => Str::slug('Example Company '.$i),
                'status' => rand(0, 1), // Random status 0 or 1
                'trial_ends_at' => Carbon::now()->addDays(rand(1, 365)), // Random trial days
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
