<?php

namespace Database\Seeders;

use App\Models\ShiftType;
use Illuminate\Database\Seeder;

class ShiftTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkShiftTypes= ShiftType::first();

        if (empty($checkShiftTypes))
        {
            // Define type data
            $shiftTypes = [
                ['name' => 'Day shift'],
                ['name' => 'Night shift'],
                ['name' => 'Special Hours'],
                // Add more type as needed
            ];

            // Insert type data into the database
            foreach ($shiftTypes as $type) {
                ShiftType::create($type);
            }
        }
    }
}
