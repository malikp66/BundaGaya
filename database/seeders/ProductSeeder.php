<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        ImageGenerator::init();

        $this->createMyzasacBrand();
        $this->createAdditionalUsers();
        $this->createGaunPestaProducts();
        $this->createGaunKebayaProducts();
        $this->createGaunMuslimProducts();
        $this->createAksesorisProducts();
        $this->createMyzasacProducts();
    }

    private function createMyzasacBrand(): void
    {
        Brand::firstOrCreate(
            ['name' => 'Myzasac'],
            [
                'description' => 'Brand tas lokal Indonesia dengan desain trendy dan harga terjangkau',
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        $aksesoris = Category::where('name', 'Aksesoris')->first();
        $myzasac = Brand::where('name', 'Myzasac')->first();

        if ($aksesoris && $myzasac) {
            $aksesoris->brands()->syncWithoutDetaching([$myzasac->id]);
        }
    }

    private function createAdditionalUsers(): void
    {
        User::firstOrCreate(
            ['email' => 'jakarta@bundagaya.com'],
            [
                'name' => 'Gaya Busana Jakarta',
                'password' => Hash::make('password'),
                'phone' => '081112223330',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preference' => 'whatsapp',
            ]
        );

        User::firstOrCreate(
            ['email' => 'surabaya@bundagaya.com'],
            [
                'name' => 'Rental Kebaya Surabaya',
                'password' => Hash::make('password'),
                'phone' => '081112223331',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preference' => 'whatsapp',
            ]
        );

        User::firstOrCreate(
            ['email' => 'myzasac@bundagaya.com'],
            [
                'name' => 'Myzasac Official',
                'password' => Hash::make('password'),
                'phone' => '081112223332',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preference' => 'whatsapp',
            ]
        );
    }

    private function createGaunPestaProducts(): void
    {
        $user = User::where('email', 'jakarta@bundagaya.com')->first();
        $category = Category::where('name', 'Formal Event')->first();
        $colors = ImageGenerator::colors();

        $products = [
            [
                'name' => 'Rose Gold Embroidery Gown',
                'brand' => 'Ivan Gunawan',
                'price' => 350000,
                'color' => 'Rose Gold',
                'color_key' => 'rose_gold',
                'material' => 'Tulle',
                'size' => 'M',
                'featured' => true,
            ],
            [
                'name' => 'Midnight Blue Sequin Dress',
                'brand' => 'Sebastian Gunawan',
                'price' => 400000,
                'color' => 'Navy',
                'color_key' => 'navy',
                'material' => 'Satin',
                'size' => 'S',
                'featured' => true,
            ],
            [
                'name' => 'Emerald Green Satin Gown',
                'brand' => 'Biyan',
                'price' => 300000,
                'color' => 'Emerald',
                'color_key' => 'emerald',
                'material' => 'Satin',
                'size' => 'L',
                'featured' => false,
            ],
            [
                'name' => 'Dusty Pink Lace Dress',
                'brand' => 'Anne Avantie',
                'price' => 450000,
                'color' => 'Dusty Pink',
                'color_key' => 'dusty_pink',
                'material' => 'Lace',
                'size' => 'M',
                'featured' => false,
            ],
            [
                'name' => 'Champagne Gold Beaded Gown',
                'brand' => 'Ivan Gunawan',
                'price' => 375000,
                'color' => 'Champagne',
                'color_key' => 'champagne',
                'material' => 'Chiffon',
                'size' => 'L',
                'featured' => false,
            ],
            [
                'name' => 'Navy Tulle Ball Gown',
                'brand' => 'Oscar Lawalata',
                'price' => 275000,
                'color' => 'Navy',
                'color_key' => 'navy',
                'material' => 'Tulle',
                'size' => 'S',
                'featured' => false,
            ],
            [
                'name' => 'Burgundy Velvet Evening Dress',
                'brand' => 'Biyan',
                'price' => 325000,
                'color' => 'Burgundy',
                'color_key' => 'burgundy',
                'material' => 'Velvet',
                'size' => 'XL',
                'featured' => false,
            ],
            [
                'name' => 'Lilac Chiffon Maxi Dress',
                'brand' => 'Sebastian Gunawan',
                'price' => 280000,
                'color' => 'Lilac',
                'color_key' => 'lilac',
                'material' => 'Chiffon',
                'size' => 'M',
                'featured' => false,
            ],
        ];

        foreach ($products as $productData) {
            $this->createProduct($user, $category, $productData);
        }
    }

    private function createGaunKebayaProducts(): void
    {
        $user = User::where('email', 'surabaya@bundagaya.com')->first();
        $category = Category::where('name', 'Wedding Guest')->first();

        $products = [
            [
                'name' => 'Kebaya Encim Putih Gading',
                'brand' => 'Anne Avantie',
                'price' => 500000,
                'color' => 'Ivory',
                'color_key' => 'ivory',
                'material' => 'Brocade',
                'size' => 'M',
                'featured' => true,
            ],
            [
                'name' => 'Kebaya Modern Scarlet',
                'brand' => 'Anne Avantie',
                'price' => 450000,
                'color' => 'Scarlet',
                'color_key' => 'scarlet',
                'material' => 'Silk',
                'size' => 'S',
                'featured' => false,
            ],
            [
                'name' => 'Kebaya Pengantin Gold Brocade',
                'brand' => 'Ivan Gunawan',
                'price' => 600000,
                'color' => 'Gold',
                'color_key' => 'gold',
                'material' => 'Brocade',
                'size' => 'M',
                'featured' => true,
            ],
            [
                'name' => 'Kebaya Classic Black Lace',
                'brand' => 'Sebastian Gunawan',
                'price' => 350000,
                'color' => 'Black',
                'color_key' => 'black',
                'material' => 'Lace',
                'size' => 'L',
                'featured' => false,
            ],
            [
                'name' => 'Kebaya Pastel Blue Embroidery',
                'brand' => 'Biyan',
                'price' => 375000,
                'color' => 'Pastel Blue',
                'color_key' => 'pastel_blue',
                'material' => 'Organza',
                'size' => 'M',
                'featured' => false,
            ],
            [
                'name' => 'Kebaya Modern Sage Green',
                'brand' => 'Oscar Lawalata',
                'price' => 300000,
                'color' => 'Sage',
                'color_key' => 'sage',
                'material' => 'Cotton',
                'size' => 'S',
                'featured' => false,
            ],
        ];

        foreach ($products as $productData) {
            $this->createProduct($user, $category, $productData);
        }
    }

    private function createGaunMuslimProducts(): void
    {
        $user = User::where('email', 'surabaya@bundagaya.com')->first();
        $category = Category::where('name', 'Wedding Guest')->first();

        $products = [
            [
                'name' => 'Maxi Dress Navy Pearl',
                'brand' => null,
                'price' => 150000,
                'color' => 'Navy',
                'color_key' => 'navy',
                'material' => 'Chiffon',
                'size' => 'All Size',
                'featured' => false,
            ],
            [
                'name' => 'Abaya Earth Tone',
                'brand' => null,
                'price' => 175000,
                'color' => 'Brown',
                'color_key' => 'brown',
                'material' => 'Cotton',
                'size' => 'All Size',
                'featured' => false,
            ],
            [
                'name' => 'Kaftan Dress Sage',
                'brand' => null,
                'price' => 200000,
                'color' => 'Sage',
                'color_key' => 'sage',
                'material' => 'Linen',
                'size' => 'L',
                'featured' => false,
            ],
            [
                'name' => 'Gaun Muslim Kaftan Gold',
                'brand' => null,
                'price' => 250000,
                'color' => 'Gold',
                'color_key' => 'gold',
                'material' => 'Satin',
                'size' => 'M',
                'featured' => false,
            ],
        ];

        foreach ($products as $productData) {
            $this->createProduct($user, $category, $productData);
        }
    }

    private function createAksesorisProducts(): void
    {
        $user = User::where('email', 'jakarta@bundagaya.com')->first();

        $aksesorisItems = [
            ['name' => 'Clutch Bag Pearl White', 'category' => 'Aksesoris', 'price' => 50000, 'color' => 'White', 'color_key' => 'white', 'material' => 'Satin', 'featured' => false],
            ['name' => 'Clutch Bag Gold Metallic', 'category' => 'Aksesoris', 'price' => 60000, 'color' => 'Gold', 'color_key' => 'gold', 'material' => 'Synthetic Leather', 'featured' => false],
            ['name' => 'Heels Stiletto Nude', 'category' => 'Aksesoris', 'price' => 75000, 'color' => 'Cream', 'color_key' => 'cream', 'material' => 'Synthetic Leather', 'featured' => false],
            ['name' => 'Heels Block Silver', 'category' => 'Aksesoris', 'price' => 80000, 'color' => 'Silver', 'color_key' => 'gray', 'material' => 'Synthetic Leather', 'featured' => false],
            ['name' => 'Kalung Pearl 3-Tier', 'category' => 'Aksesoris', 'price' => 40000, 'color' => 'White', 'color_key' => 'white', 'material' => 'Pearl', 'featured' => false],
            ['name' => 'Anting Crystal Drop', 'category' => 'Aksesoris', 'price' => 35000, 'color' => 'Gold', 'color_key' => 'gold', 'material' => 'Crystal', 'featured' => false],
            ['name' => 'Selendang Silk Pink', 'category' => 'Aksesoris', 'price' => 45000, 'color' => 'Pink', 'color_key' => 'pink', 'material' => 'Silk', 'featured' => false],
            ['name' => 'Selendang Brocade Gold', 'category' => 'Aksesoris', 'price' => 55000, 'color' => 'Gold', 'color_key' => 'gold', 'material' => 'Brocade', 'featured' => false],
        ];

        foreach ($aksesorisItems as $item) {
            $category = Category::where('name', $item['category'])->first();
            $productData = [
                'name' => $item['name'],
                'brand' => null,
                'price' => $item['price'],
                'color' => $item['color'],
                'color_key' => $item['color_key'],
                'material' => $item['material'],
                'size' => 'All Size',
                'featured' => $item['featured'],
            ];
            $this->createProduct($user, $category, $productData);
        }
    }

    private function createMyzasacProducts(): void
    {
        $user = User::where('email', 'myzasac@bundagaya.com')->first();
        $category = Category::where('name', 'Aksesoris')->first();

        $products = [
            [
                'name' => 'Myzasac Tote Bag Canvas Classic',
                'brand' => 'Myzasac',
                'price' => 75000,
                'color' => 'Natural Beige',
                'color_key' => 'natural',
                'material' => 'Canvas',
                'size' => 'All Size',
                'featured' => true,
                'description' => 'Tote bag canvas klasik dengan desain minimalis, cocok untuk penggunaan sehari-hari dan acara casual',
            ],
            [
                'name' => 'Myzasac Shoulder Bag Mini Pearl',
                'brand' => 'Myzasac',
                'price' => 85000,
                'color' => 'White',
                'color_key' => 'white',
                'material' => 'Synthetic Leather',
                'size' => 'All Size',
                'featured' => true,
                'description' => 'Shoulder bag mini dengan aksen pearl, elegan untuk acara kondangan',
            ],
            [
                'name' => 'Myzasac Crossbody Bag Saddle',
                'brand' => 'Myzasac',
                'price' => 90000,
                'color' => 'Brown',
                'color_key' => 'brown',
                'material' => 'PU Leather',
                'size' => 'All Size',
                'featured' => false,
                'description' => 'Crossbody bag model saddle dengan tali adjustable, praktis dan stylish',
            ],
            [
                'name' => 'Myzasac Clutch Envelope Gold',
                'brand' => 'Myzasac',
                'price' => 65000,
                'color' => 'Gold',
                'color_key' => 'gold',
                'material' => 'Satin',
                'size' => 'All Size',
                'featured' => true,
                'description' => 'Clutch envelope mewah warna gold, sempurna untuk pesta dan kondangan',
            ],
            [
                'name' => 'Myzasac Bucket Bag Drawstring',
                'brand' => 'Myzasac',
                'price' => 95000,
                'color' => 'Black',
                'color_key' => 'black',
                'material' => 'Canvas',
                'size' => 'All Size',
                'featured' => false,
                'description' => 'Bucket bag dengan drawstring, muat banyak barang dengan style',
            ],
            [
                'name' => 'Myzasac Sling Bag Sporty',
                'brand' => 'Myzasac',
                'price' => 70000,
                'color' => 'Navy',
                'color_key' => 'navy',
                'material' => 'Nylon',
                'size' => 'All Size',
                'featured' => false,
                'description' => 'Sling bag sporty ringan, cocok untuk aktivitas outdoor dan traveling',
            ],
            [
                'name' => 'Myzasac Backpack Mini Cute',
                'brand' => 'Myzasac',
                'price' => 100000,
                'color' => 'Pink',
                'color_key' => 'pink',
                'material' => 'Canvas',
                'size' => 'All Size',
                'featured' => false,
                'description' => 'Backpack mini lucu dengan warna pastel, trendy dan fungsional',
            ],
            [
                'name' => 'Myzasac Woven Bag Bohemian',
                'brand' => 'Myzasac',
                'price' => 110000,
                'color' => 'Natural',
                'color_key' => 'natural',
                'material' => 'Rattan/Woven',
                'size' => 'All Size',
                'featured' => false,
                'description' => 'Tas woven bohemian handmade, unik dan etnik untuk gaya bebas',
            ],
            [
                'name' => 'Myzasac Laptop Bag Sleek',
                'brand' => 'Myzasac',
                'price' => 120000,
                'color' => 'Gray',
                'color_key' => 'gray',
                'material' => 'Neoprene',
                'size' => 'All Size',
                'featured' => false,
                'description' => 'Laptop bag sleek dengan bahan neoprene, melindungi laptop dengan style',
            ],
            [
                'name' => 'Myzasac Beach Bag Oversized',
                'brand' => 'Myzasac',
                'price' => 80000,
                'color' => 'Striped Blue',
                'color_key' => 'striped',
                'material' => 'Cotton',
                'size' => 'All Size',
                'featured' => false,
                'description' => 'Beach bag oversized dengan motif striped, spacious untuk semua kebutuhan',
            ],
        ];

        foreach ($products as $productData) {
            $this->createProduct($user, $category, $productData);
        }
    }

    private function createProduct(User $user, Category $category, array $data): void
    {
        $brand = null;
        if (!empty($data['brand'])) {
            $brand = Brand::where('name', $data['brand'])->first();
        }

        $product = Product::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'brand_id' => $brand?->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? $this->generateDescription($data),
            'price_per_day' => $data['price'],
            'stock' => rand(1, 3),
            'size' => $data['size'],
            'color' => $data['color'],
            'material' => $data['material'],
            'condition' => 'good',
            'status' => 'active',
            'is_featured' => $data['featured'] ?? false,
        ]);

        $colorKey = $data['color_key'] ?? 'rose_gold';
        $colors = ImageGenerator::colors();
        $baseColor = $colors[$colorKey] ?? [180, 180, 180];

        for ($i = 0; $i < 3; $i++) {
            $variantColor = $this->varyColor($baseColor, $i);
            $photoPath = ImageGenerator::generate($data['name'], $variantColor, $i);

            ProductPhoto::create([
                'product_id' => $product->id,
                'photo_path' => $photoPath,
                'alt_text' => $data['name'] . ' - ' . ($i === 0 ? 'Tampak Depan' : ($i === 1 ? 'Tampak Samping' : 'Detail')),
                'is_primary' => $i === 0,
                'sort_order' => $i,
            ]);
        }
    }

    private function generateDescription(array $data): string
    {
        $brandText = !empty($data['brand']) ? "dari {$data['brand']}" : '';
        return "Sewa {$data['name']} {$brandText}. Warna {$data['color']}, bahan {$data['material']}. Cocok untuk acara kondangan dan pesta formal.";
    }

    private function varyColor(array $color, int $variant): array
    {
        $offset = $variant * 20;
        return [
            max(0, min(255, $color[0] - $offset)),
            max(0, min(255, $color[1] - $offset)),
            max(0, min(255, $color[2] - $offset)),
        ];
    }
}
