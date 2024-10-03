<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleHasPermissionSeederV2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::where('name', 'like', '%department%')->get();

        $role = Role::where('name', 'admin')->first();
        if (!empty($permissions->toArray())) {
            foreach ($permissions as $permission) {
                if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
                    // Attach the permission to the role
                    $role->permissions()->attach($permission->id);
                    $this->command->info("Permission with ID {$permission->id} attached to role with ID {$role->id}");
                } else {
                    $this->command->info("Permission with ID {$permission->id} already attached to role with ID {$role->id}");
                }
            }
        }
    }
}
