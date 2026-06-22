<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'primaryPhoto'])
            ->where('status', 'active');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            if (strlen($searchTerm) >= 3) {
                $query->whereFullText(['name', 'description'], $searchTerm, ['mode' => 'boolean']);
            } else {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', $request->max_price);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $products = $query->paginate(12);

        if ($startDate && $endDate) {
            $products->getCollection()->transform(function ($product) use ($startDate, $endDate) {
                $product->available_qty = $product->getAvailableQuantityForDateRange($startDate, $endDate);
                return $product;
            });
        } else {
            $products->getCollection()->transform(function ($product) {
                $product->available_qty = $product->stock;
                return $product;
            });
        }

        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        $filters = $request->only(['search', 'category_id', 'brand_id', 'min_price', 'max_price', 'start_date', 'end_date']);

        $title = 'Jual Sewa Baju & Aksesoris Kondangan';
        $description = 'Sewa baju kondangan, gaun pesta, kebaya, dan aksesoris branded. Harga mulai Rp 50.000/hari.';

        if (!empty($filters['search'])) {
            $title = "Hasil Pencarian: {$filters['search']}";
            $description = "Temukan {$products->total()} produk untuk '{$filters['search']}' di BundaGaya.";
        } elseif (!empty($filters['category_id'])) {
            $cat = $categories->firstWhere('id', $filters['category_id']);
            if ($cat) {
                $title = "Sewa {$cat->name} - Kondangan";
                $description = "Koleksi {$cat->name} terlengkap untuk acara kondangan. Sewa mulai Rp 50.000/hari.";
            }
        }

        return Inertia::render('Customer/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $filters,
            'seo' => [
                'title' => "{$title} | BundaGaya",
                'description' => $description,
                'og_image' => null,
                'og_type' => 'website',
            ],
        ]);
    }

    public function show(Product $product, Request $request)
    {
        $product->load(['category', 'brand', 'photos', 'reviews.user']);

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with(['primaryPhoto'])
            ->limit(4)
            ->get();

        $primaryImage = $product->primaryPhoto?->photo_path;
        $ogImage = $primaryImage ? url('/storage/' . $primaryImage) : null;

        $description = $product->description
            ?? "Sewa {$product->name} " . ($product->brand?->name ?? '') . " warna {$product->color}, bahan {$product->material}. Rp " . number_format($product->price_per_day, 0, ',', '.') . "/hari di BundaGaya.";

        return Inertia::render('Customer/Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'queryParams' => $request->only(['start_date', 'end_date']),
            'seo' => [
                'title' => "{$product->name} - Sewa " . ($product->brand?->name ?? '') . " | BundaGaya",
                'description' => $description,
                'og_image' => $ogImage,
                'og_type' => 'product',
            ],
        ]);
    }
}
