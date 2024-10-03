<?php

namespace Database\Seeders;

use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PermissionSeederV2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departmentPermission = Permission::where('name', 'department')->first();

        if (empty($departmentPermission)) {
            $currentTime = Carbon::now();

            $departmentPermission = Permission::create([
                'name' => 'department',
                'guard_name' => 'web'
            ]);
            $permissionId = $departmentPermission->id;
            Log::info("Permission Id == << ", [$permissionId]);
            Permission::insert([
                [
                    'name' => 'department list',
                    'guard_name' => 'web',
                    'parent_id' => $permissionId
                ],
                [
                    'name' => 'department add',
                    'guard_name' => 'web',
                    'parent_id' => $permissionId
                ],
                [
                    'name' => 'department edit',
                    'guard_name' => 'web',
                    'parent_id' => $permissionId
                ],
                [
                    'name' => 'department delete',
                    'guard_name' => 'web',
                    'parent_id' => $permissionId
                ]
            ]);
        }
    }
}
