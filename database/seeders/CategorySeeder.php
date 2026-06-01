<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeder for populating the categories table with initial data.
 *
 * This seeder creates a comprehensive set of restaurant menu categories
 * that represent common food categories in a restaurant setting. Each category
 * includes translatable names and descriptions in both English and Arabic,
 * supporting the application's multi-language requirements.
 *
 * The categories are designed to be realistic and cover the main food groups
 * typically found in a restaurant menu: appetizers, main courses, beverages,
 * desserts, and more.
 *
 * Usage:
 *   php artisan db:seed --class=CategorySeeder
 *
 */
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method creates the initial set of categories for the restaurant menu.
     * Each category is defined with both English and Arabic translations,
     * realistic descriptions, and is set to active by default.
     *
     * The categories include:
     * - Appetizers: Light dishes served before the main course
     * - Main Courses: Primary dishes of the meal
     * - Beverages: Drinks and refreshments
     * - Desserts: Sweet dishes served after the main course
     * - Soups: Liquid dishes served as starters
     * - Salads: Fresh vegetable dishes
     * - Sandwiches: Bread-based meals
     * - Grilled Items: Barbecue and grilled specialties
     *
     * @return void
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => [
                    'en' => 'Appetizers',
                    'ar' => 'المقبلات',
                ],
                'slug' => 'appetizers',
                'description' => [
                    'en' => 'Delicious starters to begin your meal with',
                    'ar' => 'مقبلات لذيذة لتبدأ بها وجبتك',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'en' => 'Main Courses',
                    'ar' => 'الأطباق الرئيسية',
                ],
                'slug' => 'main-courses',
                'description' => [
                    'en' => 'Hearty and satisfying main dishes',
                    'ar' => 'أطباق رئيسية شهية ومشبعة',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'en' => 'Beverages',
                    'ar' => 'المشروبات',
                ],
                'slug' => 'beverages',
                'description' => [
                    'en' => 'Refreshing drinks and beverages',
                    'ar' => 'مشروبات منعشة',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'en' => 'Desserts',
                    'ar' => 'الحلويات',
                ],
                'slug' => 'desserts',
                'description' => [
                    'en' => 'Sweet treats to end your meal',
                    'ar' => 'حلويات لذيذة لإنهاء وجبتك',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'en' => 'Soups',
                    'ar' => 'الشوربات',
                ],
                'slug' => 'soups',
                'description' => [
                    'en' => 'Warm and comforting soup varieties',
                    'ar' => 'أنواع شوربات دافئة ومريحة',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'en' => 'Salads',
                    'ar' => 'السلطات',
                ],
                'slug' => 'salads',
                'description' => [
                    'en' => 'Fresh and healthy salad options',
                    'ar' => 'خيارات سلطة طازجة وصحية',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'en' => 'Sandwiches',
                    'ar' => 'السندويشات',
                ],
                'slug' => 'sandwiches',
                'description' => [
                    'en' => 'Delicious sandwich varieties',
                    'ar' => 'أنواع سندويشات لذيذة',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'en' => 'Grilled Items',
                    'ar' => 'المشويات',
                ],
                'slug' => 'grilled-items',
                'description' => [
                    'en' => 'Barbecue and grilled specialties',
                    'ar' => 'تخصصات الشواء والباربيكيو',
                ],
                'is_active' => true,
            ],
        ];

        // Insert categories into the database
        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Categories seeded successfully.');
    }
}
