<?php

namespace Database\Seeders;

use App\Models\PriceTypes;
use Illuminate\Database\Seeder;

class PriceTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkPriceTypes= PriceTypes::first();

        if (empty($checkPriceTypes))
        {
        // Define type data
        $types = [
            ['name' => 'Per Sqft'],
            ['name' => 'Lumpsum'],
            ['name' => 'Single'],
            ['name' => 'Multiple'],
            ['name' => 'Percentage'],
            // Add more type as needed
        ];

        // Insert type data into the database
        foreach ($types as $type) {
            PriceTypes::create($type);
        }
      }
    }
}
