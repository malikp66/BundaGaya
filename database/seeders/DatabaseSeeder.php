<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createRolesAndPermissions();
        $this->createSettings();
        $this->createUsers();
        $this->createCategories();
        $this->createBrands();
        $this->linkBrandsToCategories();
        $this->createShops();
    }

    private function createRolesAndPermissions(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'shop_owner', 'guard_name' => 'web']);
        Role::create(['name' => 'customer', 'guard_name' => 'web']);

        Permission::create(['name' => 'manage users', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage shops', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage categories', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage brands', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage orders', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage transactions', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage withdrawals', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage settings', 'guard_name' => 'web']);

        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
    }

    private function createSettings(): void
    {
        Setting::set('admin_fee', 5000, 'Admin Fee', 'financial', 'Biaya admin tetap per transaksi (dalam Rupiah)');
    }

    private function createUsers(): void
    {
        $admin = User::create([
            'name' => 'Admin BundaGaya',
            'email' => 'admin@bundagaya.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $shopOwner = User::create([
            'name' => 'Toko Batik Solo',
            'email' => 'owner@bundagaya.com',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'role' => 'shop_owner',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $shopOwner->assignRole('shop_owner');

        $customer = User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@bundagaya.com',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $customer->assignRole('customer');
    }

    private function createCategories(): void
    {
        $categories = [
            [
                'name' => 'Baju',
                'icon' => 'shirt',
                'description' => 'Koleksi baju untuk kondangan',
                'children' => [
                    ['name' => 'Baju Batik', 'description' => 'Batik formal dan casual'],
                    ['name' => 'Baju Koko', 'description' => 'Baju koko pria'],
                    ['name' => 'Blouse', 'description' => 'Blouse wanita'],
                    ['name' => 'Kemeja', 'description' => 'Kemeja formal'],
                ],
            ],
            [
                'name' => 'Gaun',
                'icon' => 'dress',
                'description' => 'Koleksi gaun untuk acara formal',
                'children' => [
                    ['name' => 'Gaun Pesta', 'description' => 'Gaun untuk pesta'],
                    ['name' => 'Gaun Kebaya', 'description' => 'Kebaya modern'],
                    ['name' => 'Gaun Muslim', 'description' => 'Gaun muslimah'],
                ],
            ],
            [
                'name' => 'Jas',
                'icon' => 'briefcase',
                'description' => 'Jas formal pria',
                'children' => [
                    ['name' => 'Jas Formal', 'description' => 'Jas untuk acara formal'],
                    ['name' => 'Jas Casual', 'description' => 'Jas casual'],
                ],
            ],
            [
                'name' => 'Aksesoris',
                'icon' => 'accessory',
                'description' => 'Aksesoris pelengkap',
                'children' => [
                    ['name' => 'Tas', 'description' => 'Tas tangan dan clutch'],
                    ['name' => 'Sepatu', 'description' => 'Sepatu formal'],
                    ['name' => 'Perhiasan', 'description' => 'Kalung, gelang, anting'],
                    ['name' => 'Selendang', 'description' => 'Selendang dan scarf'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = Category::create($categoryData);

            foreach ($children as $child) {
                Category::create(array_merge($child, ['parent_id' => $parent->id]));
            }
        }
    }

    private function createBrands(): void
    {
        $brands = [
            ['name' => 'Batik Keris', 'description' => 'Brand batik ternama Indonesia', 'is_featured' => true],
            ['name' => 'Danar Hadi', 'description' => 'Batik premium tradisional', 'is_featured' => true],
            ['name' => 'Ivan Gunawan', 'description' => 'Desainer fashion ternama', 'is_featured' => true],
            ['name' => 'Sebastian Gunawan', 'description' => 'Haute couture Indonesia', 'is_featured' => true],
            ['name' => 'Anne Avantie', 'description' => 'Kebaya pengantin premium', 'is_featured' => true],
            ['name' => 'Biyan', 'description' => 'Fashion designer luxury', 'is_featured' => false],
            ['name' => 'Oscar Lawalata', 'description' => 'Batik contemporary', 'is_featured' => false],
            ['name' => 'Texbro', 'description' => 'Tekstil broker terpercaya', 'is_featured' => false],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }

    private function linkBrandsToCategories(): void
    {
        $baju = Category::where('name', 'Baju')->first();
        $gaun = Category::where('name', 'Gaun')->first();
        $jas = Category::where('name', 'Jas')->first();
        $aksesoris = Category::where('name', 'Aksesoris')->first();

        $batikKeris = Brand::where('name', 'Batik Keris')->first();
        $danarHadi = Brand::where('name', 'Danar Hadi')->first();
        $ivanGunawan = Brand::where('name', 'Ivan Gunawan')->first();
        $sebastianGunawan = Brand::where('name', 'Sebastian Gunawan')->first();
        $anneAvantie = Brand::where('name', 'Anne Avantie')->first();
        $biyan = Brand::where('name', 'Biyan')->first();
        $oscarLawalata = Brand::where('name', 'Oscar Lawalata')->first();

        if ($baju) {
            $baju->brands()->attach([
                $batikKeris?->id,
                $danarHadi?->id,
                $oscarLawalata?->id,
            ]);
        }

        if ($gaun) {
            $gaun->brands()->attach([
                $ivanGunawan?->id,
                $sebastianGunawan?->id,
                $biyan?->id,
            ]);
        }

        if ($jas) {
            $jas->brands()->attach([
                $ivanGunawan?->id,
                $sebastianGunawan?->id,
            ]);
        }

        if ($aksesoris) {
            $aksesoris->brands()->attach([
                $anneAvantie?->id,
                $ivanGunawan?->id,
            ]);
        }
    }

    private function createShops(): void
    {
        $shopOwner = User::where('email', 'owner@bundagaya.com')->first();

        if ($shopOwner) {
            Shop::create([
                'user_id' => $shopOwner->id,
                'name' => 'Toko Batik Solo',
                'description' => 'Menyewakan baju batik dan kebaya untuk acara kondangan',
                'phone' => '081234567891',
                'address' => 'Jl. Slamet Riyadi No. 123',
                'city' => 'Surakarta',
                'province' => 'Jawa Tengah',
                'postal_code' => '57111',
                'status' => 'active',
                'is_verified' => true,
                'commission_rate' => 10.00,
            ]);
        }
    }
}
