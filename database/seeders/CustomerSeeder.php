<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

/**
 * Seeder for populating the customers table with initial data.
 *
 * This seeder creates realistic customer data with diverse profiles
 * including names, phone numbers, addresses, and notes.
 *
 * Usage:
 *   php artisan db:seed --class=CustomerSeeder
 *
 * @author Your Name
 * @since 1.0.0
 */
class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method creates customers with realistic data including
     * names, phone numbers, addresses, and notes.
     *
     * @return void
     */
    public function run(): void
    {
        $customers = $this->getCustomersData();

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        $this->command->info('Customers seeded successfully.');
    }

    /**
     * Get the customers data array.
     *
     * This method returns a comprehensive array of customer data
     * with realistic information.
     *
     * @return array<int, array<string, mixed>> The customers data
     */
    protected function getCustomersData(): array
    {
        return [
            [
                'name' => 'Ahmed Al-Farsi',
                'phone' => '+963 944 123 456',
                'alternate_phone' => '+963 933 123 456',
                'address' => 'Damascus, Mezzeh, Street 15, Building 42',
                'notes' => 'Regular customer, prefers delivery',
            ],
            [
                'name' => 'Fatima Al-Hassan',
                'phone' => '+963 944 234 567',
                'alternate_phone' => null,
                'address' => 'Damascus, Malki, Al-Rawda Street',
                'notes' => 'VIP customer, large orders',
            ],
            [
                'name' => 'Omar Khalil',
                'phone' => '+963 944 345 678',
                'alternate_phone' => '+963 933 345 678',
                'address' => 'Damascus, Abu Rummaneh, Villa 7',
                'notes' => 'Prefers pickup, allergic to nuts',
            ],
            [
                'name' => 'Layla Mahmoud',
                'phone' => '+963 944 456 789',
                'alternate_phone' => null,
                'address' => 'Damascus, Shaalan, Apartment 12',
                'notes' => 'Orders on weekends only',
            ],
            [
                'name' => 'Kareem Al-Sayed',
                'phone' => '+963 944 567 890',
                'alternate_phone' => '+963 933 567 890',
                'address' => 'Damascus, Muhajireen, Building 88',
                'notes' => 'Corporate client, monthly billing',
            ],
            [
                'name' => 'Nour Al-Din',
                'phone' => '+963 944 678 901',
                'alternate_phone' => null,
                'address' => 'Damascus, Bab Touma, Old City',
                'notes' => 'Traditional food enthusiast',
            ],
            [
                'name' => 'Samira Youssef',
                'phone' => '+963 944 789 012',
                'alternate_phone' => '+963 933 789 012',
                'address' => 'Damascus, Rukn Ed-Din, Street 3',
                'notes' => 'Vegetarian preferences',
            ],
            [
                'name' => 'Hassan Al-Rashid',
                'phone' => '+963 944 890 123',
                'alternate_phone' => null,
                'address' => 'Damascus, Kafr Souseh, Complex A',
                'notes' => 'Lunch orders for office',
            ],
            [
                'name' => 'Amira Kaddour',
                'phone' => '+963 944 901 234',
                'alternate_phone' => '+963 933 901 234',
                'address' => 'Damascus, Qassaa, Villa 15',
                'notes' => 'Special occasions and events',
            ],
            [
                'name' => 'Youssef Al-Masri',
                'phone' => '+963 944 012 345',
                'alternate_phone' => null,
                'address' => 'Damascus, Jaramana, Main Road',
                'notes' => 'Late night orders',
            ],
            [
                'name' => 'Rania Fattal',
                'phone' => '+963 944 123 789',
                'alternate_phone' => '+963 933 123 789',
                'address' => 'Damascus, Dummar, District 4',
                'notes' => 'Family orders, children-friendly',
            ],
            [
                'name' => 'Bilal Al-Ahmad',
                'phone' => '+963 944 234 890',
                'alternate_phone' => null,
                'address' => 'Damascus, Barzeh, Sector 2',
                'notes' => 'Spicy food lover',
            ],
            [
                'name' => 'Hana Al-Saleh',
                'phone' => '+963 944 345 901',
                'alternate_phone' => '+963 933 345 901',
                'address' => 'Damascus, Al-Muhajireen, Flat 8',
                'notes' => 'Health-conscious, low sodium',
            ],
            [
                'name' => 'Jamal Al-Najjar',
                'phone' => '+963 944 456 012',
                'alternate_phone' => null,
                'address' => 'Damascus, Al-Qaboun, Industrial Zone',
                'notes' => 'Bulk orders for workers',
            ],
            [
                'name' => 'Sawsan Al-Turk',
                'phone' => '+963 944 567 123',
                'alternate_phone' => '+963 933 567 123',
                'address' => 'Damascus, Yarmouk, Camp Street',
                'notes' => 'Regular breakfast orders',
            ],
        ];
    }
}
