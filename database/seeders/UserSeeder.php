<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Seeder for populating the users table with initial data.
 *
 * This seeder creates default user accounts for the restaurant management system.
 * It uses firstOrCreate to ensure accounts are created only if they don't exist,
 * making the seeder idempotent and safe to run multiple times.
 *
 * The seeder creates three specific role-based accounts:
 * - 1 Super Administrator account with full system permissions
 * - 1 Cashier account for order management and payment processing
 * - 1 Kitchen Staff account for order preparation and kitchen operations
 *
 * All accounts use the default password 'password' for convenience in development,
 * but this should be changed in production environments.
 *
 * Usage:
 *   php artisan db:seed --class=UserSeeder
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method creates the initial set of users for the restaurant management system.
     * It uses firstOrCreate to ensure accounts are created only if they don't exist,
     * making the seeder idempotent and safe to run multiple times without creating duplicates.
     *
     * The accounts are assigned appropriate roles using the Spatie Permission
     * package to control access levels within the application:
     * - Super Administrator: Full system access and management
     * - Cashier: Order management, payment processing, and customer service
     * - Kitchen Staff: Order preparation, inventory management, and kitchen operations
     *
     * @return void
     */
    public function run(): void
    {
        // Create Super Administrator account
        $admin = User::firstOrCreate([
            'name' => 'Administrator',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('password'),
        ]);
        $role = Role::findByName('super_administrator', 'sanctum');
        $admin->assignRole($role);
        $this->command->info('Administrator account created (admin@restaurant.com)');

        // Create Cashier account
        $cashier = User::firstOrCreate([
            'name' => 'Restaurant Cashier',
            'email' => 'cashier@restaurant.com',
            'password' => Hash::make('password'),
        ]);
        $role = Role::findByName('Cashier', 'sanctum');
        $cashier->assignRole($role);
        $this->command->info('Cashier account created (cashier@restaurant.com)');

        // Create Kitchen Staff account
        $kitchen = User::firstOrCreate([
            'name' => 'Restaurant Kitchen Staff',
            'email' => 'kitchen@restaurant.com',
            'password' => Hash::make('password'),
        ]);
        $role = Role::findByName('Kitchen_staff', 'sanctum');
        $kitchen->assignRole($role);
        $this->command->info('Kitchen Staff account created (kitchen@restaurant.com)');

        $this->command->info('User seeding completed successfully.');
        $this->command->warn('Default password for all accounts: password');
    }
}
