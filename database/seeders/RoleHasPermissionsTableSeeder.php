<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleHasPermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        $checkRolePermissions= Role::first();

        if (empty($checkRolePermissions))
        {

            \DB::table('role_has_permissions')->insert(array (
                0 =>
                array (
                    'permission_id' => 1,
                    'role_id' => 1,
                ),
                1 =>
                array (
                    'permission_id' => 2,
                    'role_id' => 1,
                ),
                2 =>
                array (
                    'permission_id' => 3,
                    'role_id' => 1,
                ),
                3 =>
                array (
                    'permission_id' => 4,
                    'role_id' => 1,
                ),
                4 =>
                array (
                    'permission_id' => 5,
                    'role_id' => 1,
                ),
                5 =>
                array (
                    'permission_id' => 6,
                    'role_id' => 1,
                ),
                6 =>
                array (
                    'permission_id' => 7,
                    'role_id' => 1,
                ),
                7 =>
                array (
                    'permission_id' => 8,
                    'role_id' => 1,
                ),
                8 =>
                array (
                    'permission_id' => 9,
                    'role_id' => 1,
                ),
                9 =>
                array (
                    'permission_id' => 10,
                    'role_id' => 1,
                ),
                10 =>
                array (
                    'permission_id' => 11,
                    'role_id' => 1,
                ),
                11 =>
                array (
                    'permission_id' => 12,
                    'role_id' => 1,
                ),
                12 =>
                array (
                    'permission_id' => 13,
                    'role_id' => 1,
                ),
                13 =>
                array (
                    'permission_id' => 14,
                    'role_id' => 1,
                ),
                14 =>
                array (
                    'permission_id' => 15,
                    'role_id' => 1,
                ),
                15 =>
                array (
                    'permission_id' => 16,
                    'role_id' => 1,
                ),
                16 =>
                array (
                    'permission_id' => 17,
                    'role_id' => 1,
                ),
                17 =>
                array (
                    'permission_id' => 18,
                    'role_id' => 1,
                ),
                18 =>
                array (
                    'permission_id' => 19,
                    'role_id' => 1,
                ),
                19 =>
                array (
                    'permission_id' => 20,
                    'role_id' => 1,
                ),
                20 =>
                array (
                    'permission_id' => 21,
                    'role_id' => 1,
                ),
                21 =>
                array (
                    'permission_id' => 22,
                    'role_id' => 1,
                ),
                22 =>
                array (
                    'permission_id' => 23,
                    'role_id' => 1,
                ),
                23 =>
                array (
                    'permission_id' => 24,
                    'role_id' => 1,
                ),
                24 =>
                array (
                    'permission_id' => 25,
                    'role_id' => 1,
                ),
                25 =>
                array (
                    'permission_id' => 26,
                    'role_id' => 1,
                ),
                26 =>
                array (
                    'permission_id' => 27,
                    'role_id' => 1,
                ),
                27 =>
                array (
                    'permission_id' => 28,
                    'role_id' => 1,
                ),
                28 =>
                array (
                    'permission_id' => 29,
                    'role_id' => 1,
                ),
                29 =>
                array (
                    'permission_id' => 30,
                    'role_id' => 1,
                ),
                30 =>
                array (
                    'permission_id' => 31,
                    'role_id' => 1,
                ),
                31 =>
                array (
                    'permission_id' => 32,
                    'role_id' => 1,
                ),
                32 =>
                array (
                    'permission_id' => 33,
                    'role_id' => 1,
                ),
                33 =>
                array (
                    'permission_id' => 34,
                    'role_id' => 1,
                ),
                34 =>
                array (
                    'permission_id' => 35,
                    'role_id' => 1,
                ),
                35 =>
                array (
                    'permission_id' => 36,
                    'role_id' => 1,
                ),
                36 =>
                array (
                    'permission_id' => 37,
                    'role_id' => 1,
                ),
                37 =>
                array (
                    'permission_id' => 38,
                    'role_id' => 1,
                ),
                38 =>
                array (
                    'permission_id' => 39,
                    'role_id' => 1,
                ),
                39 =>
                array (
                    'permission_id' => 40,
                    'role_id' => 1,
                ),
                40 =>
                array (
                    'permission_id' => 41,
                    'role_id' => 1,
                ),
                41 =>
                array (
                    'permission_id' => 42,
                    'role_id' => 1,
                ),
                42 =>
                array (
                    'permission_id' => 43,
                    'role_id' => 1,
                ),
                43 =>
                array (
                    'permission_id' => 44,
                    'role_id' => 1,
                ),
                44 =>
                array (
                    'permission_id' => 45,
                    'role_id' => 1,
                ),
                45 =>
                array (
                    'permission_id' => 46,
                    'role_id' => 1,
                ),
                46 =>
                array (
                    'permission_id' => 47,
                    'role_id' => 1,
                ),
                47 =>
                array (
                    'permission_id' => 48,
                    'role_id' => 1,
                ),
                48 =>
                array (
                    'permission_id' => 49,
                    'role_id' => 1,
                ),
                49 =>
                array (
                    'permission_id' => 50,
                    'role_id' => 1,
                ),
                50 =>
                array (
                    'permission_id' => 51,
                    'role_id' => 1,
                ),
                51 =>
                array (
                    'permission_id' => 52,
                    'role_id' => 1,
                ),
                52 =>
                array (
                    'permission_id' => 53,
                    'role_id' => 1,
                ),
                53 =>
                array (
                    'permission_id' => 54,
                    'role_id' => 1,
                ),
                54 =>
                array (
                    'permission_id' => 55,
                    'role_id' => 1,
                ),
                55 =>
                array (
                    'permission_id' => 56,
                    'role_id' => 1,
                ),
                56 =>
                array (
                    'permission_id' => 57,
                    'role_id' => 1,
                ),
                57 =>
                array (
                    'permission_id' => 58,
                    'role_id' => 1,
                ),
                58 =>
                array (
                    'permission_id' => 59,
                    'role_id' => 1,
                ),
                59 =>
                array (
                    'permission_id' => 60,
                    'role_id' => 1,
                ),
                60 =>
                array (
                    'permission_id' => 61,
                    'role_id' => 1,
                ),
                61 =>
                array (
                    'permission_id' => 62,
                    'role_id' => 1,
                ),
                62 =>
                array (
                    'permission_id' => 63,
                    'role_id' => 1,
                ),
                63 =>
                array (
                    'permission_id' => 64,
                    'role_id' => 1,
                ),
                64 =>
                array (
                    'permission_id' => 65,
                    'role_id' => 1,
                ),
                65 =>
                array (
                    'permission_id' => 66,
                    'role_id' => 1,
                ),
                66 =>
                array (
                    'permission_id' => 67,
                    'role_id' => 1,
                ),
                67 =>
                array (
                    'permission_id' => 68,
                    'role_id' => 1,
                ),
                68 =>
                array (
                    'permission_id' => 69,
                    'role_id' => 1,
                ),
                69 =>
                array (
                    'permission_id' => 70,
                    'role_id' => 1,
                ),
                70 =>
                array (
                    'permission_id' => 71,
                    'role_id' => 1,
                ),
                71 =>
                array (
                    'permission_id' => 72,
                    'role_id' => 1,
                ),
                72 =>
                array (
                    'permission_id' => 73,
                    'role_id' => 1,
                ),
                73 =>
                array (
                    'permission_id' => 74,
                    'role_id' => 1,
                ),
                74 =>
                array (
                    'permission_id' => 75,
                    'role_id' => 1,
                ),
                75 =>
                array (
                    'permission_id' => 76,
                    'role_id' => 1,
                ),
                76 =>
                array (
                    'permission_id' => 77,
                    'role_id' => 1,
                ),
                77 =>
                array (
                    'permission_id' => 78,
                    'role_id' => 1,
                ),
                78 =>
                array (
                    'permission_id' => 79,
                    'role_id' => 1,
                ),
                79 =>
                array (
                    'permission_id' => 80,
                    'role_id' => 1,
                ),
                80 =>
                array (
                    'permission_id' => 81,
                    'role_id' => 1,
                ),
                81 =>
                array (
                    'permission_id' => 82,
                    'role_id' => 1,
                ),
                82 =>
                array (
                    'permission_id' => 83,
                    'role_id' => 1,
                ),
                83 =>
                array (
                    'permission_id' => 84,
                    'role_id' => 1,
                ),
                84 =>
                array (
                    'permission_id' => 85,
                    'role_id' => 1,
                ),
                85 =>
                array (
                    'permission_id' => 86,
                    'role_id' => 1,
                ),
                86 =>
                array (
                    'permission_id' => 87,
                    'role_id' => 1,
                ),
                87 =>
                array (
                    'permission_id' => 88,
                    'role_id' => 1,
                ),
                88 =>
                array (
                    'permission_id' => 89,
                    'role_id' => 1,
                ),
                89 =>
                array (
                    'permission_id' => 90,
                    'role_id' => 1,
                ),
                90 =>
                array (
                    'permission_id' => 91,
                    'role_id' => 1,
                ),
                91 =>
                array (
                    'permission_id' => 92,
                    'role_id' => 1,
                ),
                92 =>
                array (
                    'permission_id' => 93,
                    'role_id' => 1,
                ),
                93 =>
                array (
                    'permission_id' => 94,
                    'role_id' => 1,
                ),
                94 =>
                array (
                    'permission_id' => 95,
                    'role_id' => 1,
                ),
                95 =>
                array (
                    'permission_id' => 96,
                    'role_id' => 1,
                ),
                96 =>
                array (
                    'permission_id' => 97,
                    'role_id' => 1,
                ),
                97 =>
                array (
                    'permission_id' => 98,
                    'role_id' => 1,
                ),
                98 =>
                array (
                    'permission_id' => 99,
                    'role_id' => 1,
                ),
                99 =>
                array (
                    'permission_id' => 100,
                    'role_id' => 1,
                ),
                100 =>
                array (
                    'permission_id' => 102,
                    'role_id' => 1,
                ),
                101 =>
                array (
                    'permission_id' => 103,
                    'role_id' => 1,
                ),
                102 =>
                array (
                    'permission_id' => 104,
                    'role_id' => 1,
                ),
                103 =>
                array (
                    'permission_id' => 105,
                    'role_id' => 1,
                ),
                104 =>
                array (
                    'permission_id' => 8,
                    'role_id' => 2,
                ),
                105 =>
                array (
                    'permission_id' => 9,
                    'role_id' => 2,
                ),
                106 =>
                array (
                    'permission_id' => 10,
                    'role_id' => 2,
                ),
                107 =>
                array (
                    'permission_id' => 11,
                    'role_id' => 2,
                ),
                108 =>
                array (
                    'permission_id' => 13,
                    'role_id' => 2,
                ),
                109 =>
                array (
                    'permission_id' => 14,
                    'role_id' => 2,
                ),
                110 =>
                array (
                    'permission_id' => 15,
                    'role_id' => 2,
                ),
                111 =>
                array (
                    'permission_id' => 16,
                    'role_id' => 2,
                ),
                112 =>
                array (
                    'permission_id' => 18,
                    'role_id' => 2,
                ),
                113 =>
                array (
                    'permission_id' => 19,
                    'role_id' => 2,
                ),
                114 =>
                array (
                    'permission_id' => 20,
                    'role_id' => 2,
                ),
                115 =>
                array (
                    'permission_id' => 21,
                    'role_id' => 2,
                ),
                116 =>
                array (
                    'permission_id' => 23,
                    'role_id' => 2,
                ),
                117 =>
                array (
                    'permission_id' => 24,
                    'role_id' => 2,
                ),
                118 =>
                array (
                    'permission_id' => 25,
                    'role_id' => 2,
                ),
                119 =>
                array (
                    'permission_id' => 26,
                    'role_id' => 2,
                ),
                120 =>
                array (
                    'permission_id' => 28,
                    'role_id' => 2,
                ),
                121 =>
                array (
                    'permission_id' => 29,
                    'role_id' => 2,
                ),
                122 =>
                array (
                    'permission_id' => 30,
                    'role_id' => 2,
                ),
                123 =>
                array (
                    'permission_id' => 31,
                    'role_id' => 2,
                ),
                124 =>
                array (
                    'permission_id' => 33,
                    'role_id' => 2,
                ),
                125 =>
                array (
                    'permission_id' => 35,
                    'role_id' => 2,
                ),
                126 =>
                array (
                    'permission_id' => 36,
                    'role_id' => 2,
                ),
                127 =>
                array (
                    'permission_id' => 37,
                    'role_id' => 2,
                ),
                128 =>
                array (
                    'permission_id' => 39,
                    'role_id' => 2,
                ),
                129 =>
                array (
                    'permission_id' => 40,
                    'role_id' => 2,
                ),
                130 =>
                array (
                    'permission_id' => 41,
                    'role_id' => 2,
                ),
                131 =>
                array (
                    'permission_id' => 42,
                    'role_id' => 2,
                ),
                132 =>
                array (
                    'permission_id' => 44,
                    'role_id' => 2,
                ),
                133 =>
                array (
                    'permission_id' => 45,
                    'role_id' => 2,
                ),
                134 =>
                array (
                    'permission_id' => 46,
                    'role_id' => 2,
                ),
                135 =>
                array (
                    'permission_id' => 47,
                    'role_id' => 2,
                ),
                136 =>
                array (
                    'permission_id' => 49,
                    'role_id' => 2,
                ),
                137 =>
                array (
                    'permission_id' => 50,
                    'role_id' => 2,
                ),
                138 =>
                array (
                    'permission_id' => 51,
                    'role_id' => 2,
                ),
                139 =>
                array (
                    'permission_id' => 52,
                    'role_id' => 2,
                ),
                140 =>
                array (
                    'permission_id' => 53,
                    'role_id' => 2,
                ),
                141 =>
                array (
                    'permission_id' => 54,
                    'role_id' => 2,
                ),
                142 =>
                array (
                    'permission_id' => 56,
                    'role_id' => 2,
                ),
                143 =>
                array (
                    'permission_id' => 57,
                    'role_id' => 2,
                ),
                144 =>
                array (
                    'permission_id' => 59,
                    'role_id' => 2,
                ),
                145 =>
                array (
                    'permission_id' => 60,
                    'role_id' => 2,
                ),
                146 =>
                array (
                    'permission_id' => 61,
                    'role_id' => 2,
                ),
                147 =>
                array (
                    'permission_id' => 62,
                    'role_id' => 2,
                ),
                148 =>
                array (
                    'permission_id' => 64,
                    'role_id' => 2,
                ),
                149 =>
                array (
                    'permission_id' => 65,
                    'role_id' => 2,
                ),
                150 =>
                array (
                    'permission_id' => 66,
                    'role_id' => 2,
                ),
                151 =>
                array (
                    'permission_id' => 67,
                    'role_id' => 2,
                ),
                152 =>
                array (
                    'permission_id' => 69,
                    'role_id' => 2,
                ),
                153 =>
                array (
                    'permission_id' => 70,
                    'role_id' => 2,
                ),
                154 =>
                array (
                    'permission_id' => 71,
                    'role_id' => 2,
                ),
                155 =>
                array (
                    'permission_id' => 72,
                    'role_id' => 2,
                ),
                156 =>
                array (
                    'permission_id' => 73,
                    'role_id' => 2,
                ),
                157 =>
                array (
                    'permission_id' => 74,
                    'role_id' => 2,
                ),
                158 =>
                array (
                    'permission_id' => 76,
                    'role_id' => 2,
                ),
                159 =>
                array (
                    'permission_id' => 77,
                    'role_id' => 2,
                ),
                160 =>
                array (
                    'permission_id' => 78,
                    'role_id' => 2,
                ),
                161 =>
                array (
                    'permission_id' => 79,
                    'role_id' => 2,
                ),
                162 =>
                array (
                    'permission_id' => 80,
                    'role_id' => 2,
                ),
                163 =>
                array (
                    'permission_id' => 81,
                    'role_id' => 2,
                ),
                164 =>
                array (
                    'permission_id' => 83,
                    'role_id' => 2,
                ),
                165 =>
                array (
                    'permission_id' => 84,
                    'role_id' => 2,
                ),
                166 =>
                array (
                    'permission_id' => 85,
                    'role_id' => 2,
                ),
                167 =>
                array (
                    'permission_id' => 86,
                    'role_id' => 2,
                ),
                168 =>
                array (
                    'permission_id' => 88,
                    'role_id' => 2,
                ),
                169 =>
                array (
                    'permission_id' => 89,
                    'role_id' => 2,
                ),
                170 =>
                array (
                    'permission_id' => 90,
                    'role_id' => 2,
                ),
                171 =>
                array (
                    'permission_id' => 91,
                    'role_id' => 2,
                ),
                172 =>
                array (
                    'permission_id' => 93,
                    'role_id' => 2,
                ),
                173 =>
                array (
                    'permission_id' => 95,
                    'role_id' => 2,
                ),
                174 =>
                array (
                    'permission_id' => 96,
                    'role_id' => 2,
                ),
                175 =>
                array (
                    'permission_id' => 97,
                    'role_id' => 2,
                ),
                176 =>
                array (
                    'permission_id' => 98,
                    'role_id' => 2,
                ),
                177 =>
                array (
                    'permission_id' => 99,
                    'role_id' => 2,
                ),
                178 =>
                array (
                    'permission_id' => 100,
                    'role_id' => 2,
                ),
                179 =>
                array (
                    'permission_id' => 102,
                    'role_id' => 2,
                ),
                180 =>
                array (
                    'permission_id' => 103,
                    'role_id' => 2,
                ),
                181 =>
                array (
                    'permission_id' => 104,
                    'role_id' => 2,
                ),
                182 =>
                array (
                    'permission_id' => 105,
                    'role_id' => 2,
                ),
                183 =>
                array (
                    'permission_id' => 28,
                    'role_id' => 3,
                ),
                184 =>
                array (
                    'permission_id' => 31,
                    'role_id' => 3,
                ),
                185 =>
                array (
                    'permission_id' => 33,
                    'role_id' => 3,
                ),
                186 =>
                array (
                    'permission_id' => 13,
                    'role_id' => 4,
                ),
                187 =>
                array (
                    'permission_id' => 14,
                    'role_id' => 4,
                ),
                188 =>
                array (
                    'permission_id' => 15,
                    'role_id' => 4,
                ),
                189 =>
                array (
                    'permission_id' => 16,
                    'role_id' => 4,
                ),
                190 =>
                array (
                    'permission_id' => 23,
                    'role_id' => 4,
                ),
                191 =>
                array (
                    'permission_id' => 24,
                    'role_id' => 4,
                ),
                192 =>
                array (
                    'permission_id' => 25,
                    'role_id' => 4,
                ),
                193 =>
                array (
                    'permission_id' => 26,
                    'role_id' => 4,
                ),
                194 =>
                array (
                    'permission_id' => 28,
                    'role_id' => 4,
                ),
                195 =>
                array (
                    'permission_id' => 30,
                    'role_id' => 4,
                ),
                196 =>
                array (
                    'permission_id' => 31,
                    'role_id' => 4,
                ),
                197 =>
                array (
                    'permission_id' => 33,
                    'role_id' => 4,
                ),
                198 =>
                array (
                    'permission_id' => 54,
                    'role_id' => 4,
                ),
                199 =>
                array (
                    'permission_id' => 59,
                    'role_id' => 4,
                ),
                200 =>
                array (
                    'permission_id' => 60,
                    'role_id' => 4,
                ),
                201 =>
                array (
                    'permission_id' => 61,
                    'role_id' => 4,
                ),
                202 =>
                array (
                    'permission_id' => 62,
                    'role_id' => 4,
                ),
                203 =>
                array (
                    'permission_id' => 69,
                    'role_id' => 4,
                ),
                204 =>
                array (
                    'permission_id' => 70,
                    'role_id' => 4,
                ),
                205 =>
                array (
                    'permission_id' => 71,
                    'role_id' => 4,
                ),
                206 =>
                array (
                    'permission_id' => 72,
                    'role_id' => 4,
                ),
                207 =>
                array (
                    'permission_id' => 74,
                    'role_id' => 4,
                ),
                208 =>
                array (
                    'permission_id' => 28,
                    'role_id' => 5,
                ),
                209 =>
                array (
                    'permission_id' => 31,
                    'role_id' => 5,
                ),
                210 =>
                array (
                    'permission_id' => 33,
                    'role_id' => 5,
                ),

                211 =>
                array (
                    'permission_id' => 106,
                    'role_id' => 1,
                ),

                212 =>
                array (
                    'permission_id' => 107,
                    'role_id' => 1,
                ),

                213 =>
                array (
                    'permission_id' => 108,
                    'role_id' => 1,
                ),
                214 =>
                array (
                    'permission_id' => 109,
                    'role_id' => 1,
                ),
                215 =>
                array (
                    'permission_id' => 110,
                    'role_id' => 1,
                ),
            ));

         }

    }
}
