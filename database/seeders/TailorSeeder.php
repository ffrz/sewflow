<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TailorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Tailor::factory(20)->create();
    }
}
