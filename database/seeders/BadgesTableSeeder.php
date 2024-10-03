<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badges;

class BadgesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkBadges = Badges::first();

        if (empty($checkBadges))
        {
            // Define badge data
            $badges = [
                ['name' => 'Light Blue', 'badge_color' => '#ADD8E6'],
                ['name' => 'Light Yellow', 'badge_color' => '#FFFFE0'],
                ['name' => 'Lemon Yellow', 'badge_color' => '#FFF700'],
                ['name' => 'Gold Yellow', 'badge_color' => '#FFD700'],
                ['name' => 'Sunflower Yellow', 'badge_color' => '#FFAC33'],
                ['name' => 'Red', 'badge_color' => '#FF0000'],
                // Add more badges as needed
            ];

            // Insert badge data into the database
            foreach ($badges as $badge) {
                Badges::create($badge);
            }
        }
    }
}
