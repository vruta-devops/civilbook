<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaterialUnits;

class MaterialUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkMaterialUnits = MaterialUnits::first();

        if (empty($checkMaterialUnits))
        {
        // Define MaterialUnits data
        $types = [
            ['name' => 'Piece'],
            ['name' => 'Ton'],
            ['name' => 'Kg'],
            ['name' => 'Bag'],
            ['name' => 'Liter'],
            ['name' => 'R.Ft'],
            ['name' => 'Meter'],
            ['name' => 'Sq.Ft'],
            ['name' => 'Sq.M'],
            ['name' => 'Cu.ft'],
            ['name' => 'Bundle'],
            ['name' => 'Density Kn/Cu.Ft'],
            ['name' => 'Unit Weight Kn/Cu.Ft'],
            ['name' => 'Unit area Kn/Sq.Ft'],
            ['name' => 'Brass'],
            // Add more MaterialUnits as needed
        ];

        // Insert MaterialUnits data into the database
        foreach ($types as $type) {
            MaterialUnits::create($type);
        }
      }
    }
}
