<?php

namespace Database\Seeders;

use App\Models\ShiftHour;
use App\Models\ShiftType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShiftHoursSeeder extends Seeder
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
        $shiftTypes = new ShiftType();
        $currentTimeStamp = Carbon::now();

        $shiftHoursData = [
            // Day Shift
            [
                'shift_type_id' => $shiftTypes->where('name', 'Day Shift')->first()->id,
                'hours_from' => '09:00:00',
                'hours_to' => '17:00:00',
                'created_at' => $currentTimeStamp,
                'updated_at' => $currentTimeStamp,
            ],
            // Night Shift
            [
                'shift_type_id' => $shiftTypes->where('name', 'Night Shift')->first()->id,
                'hours_from' => '21:00:00',
                'hours_to' => '05:00:00',
                'created_at' => $currentTimeStamp,
                'updated_at' => $currentTimeStamp,
            ],
            // Special Hours (replace with your specific timings)
            [
                'shift_type_id' => $shiftTypes->where('name', 'Special Hours')->first()->id,
                'hours_from' => '10:00:00',
                'hours_to' => '18:00:00',
                'created_at' => $currentTimeStamp,
                'updated_at' => $currentTimeStamp,
            ],
            [
                'shift_type_id' => $shiftTypes->where('name', 'Special Hours')->first()->id,
                'hours_from' => 'Other',
                'hours_to' => null,
                'created_at' => $currentTimeStamp,
                'updated_at' => $currentTimeStamp,
            ],
        ];
        ShiftHour::insert($shiftHoursData);
    }

    }
}
