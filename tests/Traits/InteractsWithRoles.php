<?php

namespace Tests\Traits;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

// trait function:
//    - Create a User
//    - Assign the role
//    - Log the user in using Sanctum
trait InteractsWithRoles
{
    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();

        $user->assignRole($role);

        return $user;
    }


    protected function actingAsRole(string $role): User
    {
        $user = $this->createUserWithRole($role);

        Sanctum::actingAs($user);

        return $user;
    }
}