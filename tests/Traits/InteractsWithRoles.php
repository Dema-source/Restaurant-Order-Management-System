<?php

namespace Tests\Traits;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// trait function:
//    - Clears the cache and reloads the roles from the database
//    - Verify the existence of the role
//    - Create a User
//    - Assign the role
//    - Log the user in using Sanctum
trait InteractsWithRoles
{
    protected function createUserWithRole(string $role): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate([
            'name' => $role,
            'guard_name' => 'sanctum',
        ]);

        $user = User::factory()->create();

        $user->assignRole($role);

        return $user;
    }

    protected function actingAsRole(string $role): User
    {
        $user = $this->createUserWithRole($role);
        
        // Treat this user as the current user
        Sanctum::actingAs($user);

        return $user;
    }
}
