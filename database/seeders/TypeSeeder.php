<?php

namespace Database\Seeders;

use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = Type::first();

        if (empty($types)) {
            $currentTimestamp = Carbon::now();
            Type::insert([
                [
                    'name' => 'Daily Wage',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Labour contract',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Material Contract',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Contract  Rental',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Rental Per Day',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Delivery at Your Place',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Sales with Installation',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Service At Site',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Service At Work Station',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Supplier',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Manufacture',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Designing / Documentation',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Project Management',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Full Time',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Part Time',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Special hours',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Agricultural',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Non Agricultural',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'New',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Mortgage',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Refinance',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Delivery at Our Place',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Sales',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
            ]);
        }
    }
}
