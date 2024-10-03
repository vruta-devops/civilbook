<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CertificatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $certificates = Certificate::first();

        if (empty($certificates)) {
            $currentTimestamp = Carbon::now();

            Certificate::insert([
                [
                    'name' => 'Aadhar',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Driving Licence',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Establishment Certificate',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Firm Registration Certificate',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'GST',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Qualification Certificate ',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
                [
                    'name' => 'Technical Certificate',
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ],
            ]);
        }
    }
}
