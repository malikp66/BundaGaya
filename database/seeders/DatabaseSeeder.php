<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createSettings();
        $this->createUsers();
        $this->createCategories();
        $this->createBrands();
        $this->linkBrandsToCategories();
        $this->call(ProductSeeder::class);
    }

    private function createSettings(): void
    {
        Setting::set('admin_fee', 5000, 'Admin Fee', 'financial', 'Biaya admin tetap per transaksi (dalam Rupiah)');
    }

    private function createUsers(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@bundagaya.com'],
            [
                'name' => 'Admin BundaGaya',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'owner@bundagaya.com'],
            [
                'name' => 'Toko Batik Solo',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@bundagaya.com'],
            [
                'name' => 'Customer Demo',
                'password' => Hash::make('password'),
                'phone' => '081234567892',
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    private function createCategories(): void
    {
        $categories = [
            ['name' => 'Wedding Guest', 'icon' => 'dress', 'description' => 'Koleksi untuk tamu undangan pernikahan'],
            ['name' => 'Graduation', 'icon' => 'graduation', 'description' => 'Koleksi untuk acara wisuda'],
            ['name' => 'Formal Event', 'icon' => 'briefcase', 'description' => 'Koleksi untuk acara formal'],
            ['name' => 'Bride Event', 'icon' => 'heart', 'description' => 'Koleksi khusus untuk pengantin'],
            ['name' => 'Aksesoris', 'icon' => 'accessory', 'description' => 'Aksesoris pelengkap'],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
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
        $weddingGuest = Category::where('name', 'Wedding Guest')->first();
        $formalEvent = Category::where('name', 'Formal Event')->first();
        $brideEvent = Category::where('name', 'Bride Event')->first();
        $aksesoris = Category::where('name', 'Aksesoris')->first();

        $ivanGunawan = Brand::where('name', 'Ivan Gunawan')->first();
        $sebastianGunawan = Brand::where('name', 'Sebastian Gunawan')->first();
        $anneAvantie = Brand::where('name', 'Anne Avantie')->first();
        $biyan = Brand::where('name', 'Biyan')->first();
        $oscarLawalata = Brand::where('name', 'Oscar Lawalata')->first();

        if ($weddingGuest) {
            $weddingGuest->brands()->attach([
                $anneAvantie?->id,
                $ivanGunawan?->id,
                $oscarLawalata?->id,
            ]);
        }

        if ($formalEvent) {
            $formalEvent->brands()->attach([
                $ivanGunawan?->id,
                $sebastianGunawan?->id,
                $biyan?->id,
            ]);
        }

        if ($brideEvent) {
            $brideEvent->brands()->attach([
                $anneAvantie?->id,
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

}
