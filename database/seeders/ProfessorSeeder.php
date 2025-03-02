<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfessorData;

class ProfessorSeeder extends Seeder
{
    public function run()
    {
        // Seed at least one professor manually to avoid FK issues
        ProfessorData::create([
            'id' => 1, // Ensure it matches IDs used in your JSON
            'name' => 'Dr. John Doe',
            'email' => 'johndoe@example.com',
        ]);
    }
}
