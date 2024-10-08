<?php

namespace Database\Seeders;

use App\Models\ProviderType;
use Illuminate\Database\Seeder;

class ProviderTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        $checkProviderType= ProviderType::first();

        if (empty($checkProviderType))
        {
            \DB::table('provider_types')->insert(array (
                0 =>
                array (
                    'commission' => 20.0,
                    'created_at' => '2021-06-10 11:43:51',
                    'id' => 1,
                    'name' => 'Company',
                    'status' => 1,
                    'type'=>'percent',
                    'updated_at' => NULL,
                    'deleted_at' => NULL,
                ),
                1 =>
                array (
                    'commission' => 5.0,
                    'created_at' => '2021-06-10 11:58:53',
                    'id' => 2,
                    'name' => 'Freelance',
                    'status' => 1,
                    'type'=>'fixed',
                    'updated_at' => NULL,
                    'deleted_at' => NULL,
                ),
            ));
        }

    }
}
