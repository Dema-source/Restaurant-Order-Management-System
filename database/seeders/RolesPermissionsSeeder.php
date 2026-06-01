<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Definition of basic roles
        $roles = [
            'super_administrator',
            'Cashier',
            'Kitchen_staff',
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'sanctum',
            ]);
        }
    }
}
