<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Seeder for populating the menu_items table with initial data.
 *
 * This seeder creates a comprehensive set of menu items for the restaurant.
 * Each item is associated with a category and includes translatable names and
 * descriptions in both English and Arabic, realistic pricing, and availability
 * status. The items are designed to represent a diverse and appealing menu.
 *
 * The seeder first ensures that categories exist before creating menu items,
 * establishing proper foreign key relationships. Each category receives
 * multiple menu items to create a realistic menu structure.
 *
 * Usage:
 *   php artisan db:seed --class=MenuItemSeeder
 *
 * Note: Run CategorySeeder before this seeder to ensure categories exist.
 *
 */
class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method creates menu items for each category in the database.
     * It first verifies that categories exist, then creates multiple menu items
     * per category with realistic data including translatable content, pricing,
     * and availability status.
     *
     * The menu items cover various price ranges and include both popular
     * and specialty dishes to create a diverse menu offering.
     *
     * @return void
     */
    public function run(): void
    {
        // Ensure categories exist before creating menu items
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        $menuItems = $this->getMenuItemsData();

        // Create menu items with their respective categories
        foreach ($menuItems as $itemData) {
            $category = $categories->where('slug', $itemData['category_slug'])->first();

            if ($category) {
                MenuItem::create([
                    'category_id' => $category->id,
                    'name' => $itemData['name'],
                    'slug' => $itemData['slug'],
                    'description' => $itemData['description'],
                    'price' => $itemData['price'],
                    'image' => $itemData['image'] ?? null,
                    'is_available' => $itemData['is_available'] ?? true,
                ]);
            }
        }

        $this->command->info('Menu items seeded successfully.');
    }

    /**
     * Get the menu items data array.
     *
     * This method returns a comprehensive array of menu item data organized
     * by category. Each item includes translatable names and descriptions,
     * realistic pricing, and availability status.
     *
     * @return array<int, array<string, mixed>> The menu items data
     */
    protected function getMenuItemsData(): array
    {
        return [
            // Appetizers
            [
                'category_slug' => 'appetizers',
                'name' => [
                    'en' => 'Hummus',
                    'ar' => 'حمص',
                ],
                'slug' => 'hummus',
                'description' => [
                    'en' => 'Creamy chickpea dip with olive oil and paprika',
                    'ar' => 'غموس الحمص الكريمي مع زيت الزيتون والبابريكا',
                ],
                'price' => 8.50,
                'is_available' => true,
            ],
            [
                'category_slug' => 'appetizers',
                'name' => [
                    'en' => 'Falafel Plate',
                    'ar' => 'طبق Falafel',
                ],
                'slug' => 'falafel-plate',
                'description' => [
                    'en' => 'Crispy falafel balls served with tahini sauce',
                    'ar' => 'كرات الفلافل المقرمشة تقدم مع صلصة الطحينة',
                ],
                'price' => 9.00,
                'is_available' => true,
            ],
            [
                'category_slug' => 'appetizers',
                'name' => [
                    'en' => 'Stuffed Grape Leaves',
                    'ar' => 'ورق عنب محشي',
                ],
                'slug' => 'stuffed-grape-leaves',
                'description' => [
                    'en' => 'Tender grape leaves stuffed with rice and herbs',
                    'ar' => 'أوراق العنب الرقيقة المحشوة بالأرز والأعشاب',
                ],
                'price' => 10.50,
                'is_available' => true,
            ],

            // Main Courses
            [
                'category_slug' => 'main-courses',
                'name' => [
                    'en' => 'Grilled Chicken',
                    'ar' => 'دجاج مشوي',
                ],
                'slug' => 'grilled-chicken',
                'description' => [
                    'en' => 'Marinated chicken breast grilled to perfection',
                    'ar' => 'صدر الدجاج المتتبل المشوي بإتقان',
                ],
                'price' => 18.00,
                'is_available' => true,
            ],
            [
                'category_slug' => 'main-courses',
                'name' => [
                    'en' => 'Lamb Kebab',
                    'ar' => 'كباب لحم',
                ],
                'slug' => 'lamb-kebab',
                'description' => [
                    'en' => 'Tender lamb skewers with aromatic spices',
                    'ar' => 'أسياخ لحم الضأن الطري مع التوابل العطرية',
                ],
                'price' => 22.00,
                'is_available' => true,
            ],
            [
                'category_slug' => 'main-courses',
                'name' => [
                    'en' => 'Mixed Grill Platter',
                    'ar' => 'طبق مشويات مشكل',
                ],
                'slug' => 'mixed-grill-platter',
                'description' => [
                    'en' => 'Assortment of grilled meats served with rice',
                    'ar' => 'تشكيلة من اللحوم المشوية تقدم مع الأرز',
                ],
                'price' => 35.00,
                'is_available' => true,
            ],

            // Beverages
            [
                'category_slug' => 'beverages',
                'name' => [
                    'en' => 'Fresh Lemonade',
                    'ar' => 'عصير ليمون طازج',
                ],
                'slug' => 'fresh-lemonade',
                'description' => [
                    'en' => 'Refreshing homemade lemonade with mint',
                    'ar' => 'عصير الليمون المنعش محلي الصنع مع النعناع',
                ],
                'price' => 4.50,
                'is_available' => true,
            ],
            [
                'category_slug' => 'beverages',
                'name' => [
                    'en' => 'Turkish Coffee',
                    'ar' => 'قهوة تركية',
                ],
                'slug' => 'turkish-coffee',
                'description' => [
                    'en' => 'Traditional strong coffee served with delight',
                    'ar' => 'قهوة قوية تقليدية تقدم مع التحلية',
                ],
                'price' => 3.50,
                'is_available' => true,
            ],

            // Desserts
            [
                'category_slug' => 'desserts',
                'name' => [
                    'en' => 'Baklava',
                    'ar' => 'بقلاوة',
                ],
                'slug' => 'baklava',
                'description' => [
                    'en' => 'Sweet phyllo pastry with nuts and honey syrup',
                    'ar' => 'عجينة الفيلو الحلوة مع المكسرات وشراب العسل',
                ],
                'price' => 7.00,
                'is_available' => true,
            ],
            [
                'category_slug' => 'desserts',
                'name' => [
                    'en' => 'Kunafa',
                    'ar' => 'كنافة',
                ],
                'slug' => 'kunafa',
                'description' => [
                    'en' => 'Sweet cheese pastry soaked in syrup',
                    'ar' => 'عجينة الجبن الحلوة المشبعة بالشراب',
                ],
                'price' => 8.00,
                'is_available' => true,
            ],

            // Soups
            [
                'category_slug' => 'soups',
                'name' => [
                    'en' => 'Lentil Soup',
                    'ar' => 'شوربة عدس',
                ],
                'slug' => 'lentil-soup',
                'description' => [
                    'en' => 'Hearty lentil soup with cumin and lemon',
                    'ar' => 'شوربة عدس مشبعة مع الكمون والليمون',
                ],
                'price' => 6.00,
                'is_available' => true,
            ],
            [
                'category_slug' => 'soups',
                'name' => [
                    'en' => 'Chicken Soup',
                    'ar' => 'شوربة دجاج',
                ],
                'slug' => 'chicken-soup',
                'description' => [
                    'en' => 'Clear chicken soup with vegetables',
                    'ar' => 'شوربة دجاج صافية مع الخضروات',
                ],
                'price' => 7.50,
                'is_available' => true,
            ],

            // Salads
            [
                'category_slug' => 'salads',
                'name' => [
                    'en' => 'Greek Salad',
                    'ar' => 'سلطة يونانية',
                ],
                'slug' => 'greek-salad',
                'description' => [
                    'en' => 'Fresh vegetables with feta cheese and olives',
                    'ar' => 'خضروات طازجة مع جبنة الفيتا والزيتون',
                ],
                'price' => 9.50,
                'is_available' => true,
            ],
            [
                'category_slug' => 'salads',
                'name' => [
                    'en' => 'Tabbouleh',
                    'ar' => 'تبولة',
                ],
                'slug' => 'tabbouleh',
                'description' => [
                    'en' => 'Parsley salad with tomatoes and bulgur',
                    'ar' => 'سلطة البقدونس مع الطماطم والبرغل',
                ],
                'price' => 8.00,
                'is_available' => true,
            ],

            // Sandwiches
            [
                'category_slug' => 'sandwiches',
                'name' => [
                    'en' => 'Chicken Shawarma',
                    'ar' => 'شاورما دجاج',
                ],
                'slug' => 'chicken-shawarma',
                'description' => [
                    'en' => 'Marinated chicken in pita bread with garlic sauce',
                    'ar' => 'دجاج متبل في خبز البيتا مع صلصة الثوم',
                ],
                'price' => 12.00,
                'is_available' => true,
            ],
            [
                'category_slug' => 'sandwiches',
                'name' => [
                    'en' => 'Beef Shawarma',
                    'ar' => 'شاورما لحم',
                ],
                'slug' => 'beef-shawarma',
                'description' => [
                    'en' => 'Spiced beef in pita bread with tahini',
                    'ar' => 'لحم متبول في خبز البيتا مع الطحينة',
                ],
                'price' => 14.00,
                'is_available' => true,
            ],

            // Grilled Items
            [
                'category_slug' => 'grilled-items',
                'name' => [
                    'en' => 'Shish Tawook',
                    'ar' => 'شيش طاووق',
                ],
                'slug' => 'shish-tawook',
                'description' => [
                    'en' => 'Marinated chicken skewers with garlic sauce',
                    'ar' => 'أسياخ الدجاج المتتبلة مع صلصة الثوم',
                ],
                'price' => 16.00,
                'is_available' => true,
            ],
            [
                'category_slug' => 'grilled-items',
                'name' => [
                    'en' => 'Adana Kebab',
                    'ar' => 'كباب أضنة',
                ],
                'slug' => 'adana-kebab',
                'description' => [
                    'en' => 'Spicy minced lamb skewers',
                    'ar' => 'أسياخ لحم الضأن المفروم الحار',
                ],
                'price' => 19.00,
                'is_available' => true,
            ],
        ];
    }
}
